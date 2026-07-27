# Apex Nested Widget Development Guide

This guide documents the architecture required for real Elementor nested widgets in
Apex Addons. It was written after resolving the Content Switcher failure in which the
widget appeared in the editor, but clicking **Add Item** crashed with:

```text
Uncaught TypeError: Cannot read properties of undefined (reading 'defaults')
at Repeater.getDefaults
at Repeater.onButtonAddRowClick
```

Use this guide for every future nested widget. A widget is not considered correctly
nested merely because its PHP control uses `Control_Nested_Repeater` or because child
containers appear in the editor.

## Root cause of the Content Switcher failure

The PHP widget correctly extended `Widget_Nested_Base`, declared a nested repeater,
provided default child containers, and returned valid initial configuration. However,
the editor did not have a JavaScript element-type registration for
`eas-nested-content-switcher`.

Elementor therefore created the widget with its ordinary widget model instead of its
nested model. When the nested repeater attempted to add a panel, Elementor internally
read:

```js
widgetContainer.model.config.defaults
```

The ordinary model had no nested `config`, so `config.defaults` caused the exception.
The repeater data itself was not the problem.

The fix was to register the widget name with Elementor's editor as a
`NestedElementBase` and return Elementor's `NestedView`. The implementation is in:

- `assets/js/nested-content-switcher-editor.js`
- `apex-addons-for-elementor.php` under the editor asset enqueue method

The existing Nested Carousel uses the same architecture in
`assets/js/nested-slider-editor.js`.

## A nested widget has two required registrations

### 1. PHP widget registration

The PHP class must extend Elementor's nested widget base:

```php
use Elementor\Modules\NestedElements\Base\Widget_Nested_Base;
use Elementor\Modules\NestedElements\Controls\Control_Nested_Repeater;

class Example_Nested_Widget extends Widget_Nested_Base {
    public function get_name() {
        return 'apexadfo-example-nested-widget';
    }
}
```

The widget must also implement the nested child contract:

- `get_default_children_elements()` returns the initial child elements. Containers
  are normally the safest child type.
- `get_default_repeater_title_setting_key()` returns the repeater field used as the
  child label.
- `get_default_children_title()` returns Elementor's fallback child title.
- `get_default_children_placeholder_selector()` identifies the rendered DOM element
  that contains the nested children.
- `get_initial_config()` merges the parent configuration and enables the required
  nested repeater behavior.

Example:

```php
protected function get_initial_config(): array {
    return array_merge(
        parent::get_initial_config(),
        [
            'support_improved_repeaters' => true,
            'target_container'           => [ '.apexadfo-example-navigation' ],
            'node'                       => 'button',
        ]
    );
}
```

Never replace the parent configuration. Always merge with
`parent::get_initial_config()`; Elementor supplies required nested metadata there.

The panel control must use the nested repeater control type:

```php
$this->add_control(
    'panels',
    [
        'type'               => Control_Nested_Repeater::CONTROL_TYPE,
        'fields'             => $repeater->get_controls(),
        'frontend_available' => true,
        'default'            => $default_panels,
        'title_field'        => '{{{ panel_label }}}',
    ]
);
```

Important invariants:

- The number and order of default repeater rows must match the number and order of
  default child containers.
- Adding, duplicating, removing, or sorting a repeater row must keep its paired child
  container synchronized.
- The placeholder selector must exist in the widget's rendered HTML.
- The setting returned by `get_default_repeater_title_setting_key()` must exist in
  the repeater fields.
- The widget `get_name()` value is a permanent machine identifier. Do not casually
  rename it after release because saved Elementor documents depend on it.

### 2. Elementor editor JavaScript registration

This step is mandatory and was the missing part of the broken Content Switcher.

Create an editor-only JavaScript file that registers the exact PHP `get_name()` value:

```js
class ApexadfoExampleNestedWidget extends
    elementor.modules.elements.types.NestedElementBase {
    getType() {
        return 'apexadfo-example-nested-widget';
    }

    getView() {
        return $e.components.get( 'nested-elements' ).exports.NestedView;
    }
}

elementor.elementsManager.registerElementType(
    new ApexadfoExampleNestedWidget()
);
```

Requirements:

- `getType()` must exactly match the PHP `get_name()` value, including prefix and
  hyphens.
- Extend `NestedElementBase` using native ES6 `class` syntax. Do not use Backbone
  `.extend()` for this native class.
- Return the `NestedView` exported by Elementor's `nested-elements` component.
- Register only if the element type has not already been registered.
- Guard every Elementor editor dependency because asset load order can vary.
- If dependencies are not ready immediately, retry on
  `elementor/nested-element-type-loaded`.
- Keep the file editor-only. It is unnecessary on the public frontend.

Enqueue it through WordPress APIs with the `nested-elements` dependency:

```php
wp_enqueue_script(
    'apexadfo-example-nested-editor-js',
    plugins_url( 'assets/js/example-nested-editor.js', __FILE__ ),
    [ 'jquery', 'nested-elements' ],
    APEXADFO_VERSION,
    true
);
```

Do not print an inline `<script>` block. Use a uniquely prefixed handle and an
external asset, in accordance with the WordPress.org review checklist.

## Editor loading order

Nested APIs may not exist when the file first executes. The registration helper must:

1. Attempt immediate registration.
2. Verify `window.elementor`, `NestedElementBase`, `$e`, `NestedView`, and
   `elementor.elementsManager` exist.
3. Subscribe to `elementor/nested-element-type-loaded` if the first attempt cannot
   register.
4. Avoid duplicate registration by checking
   `elementor.elementsManager.elementTypes[ widgetName ]`.

After adding or changing an editor registration, fully reload Elementor. An editor
tab that was already open can retain the old element model even when the PHP and
JavaScript files on disk are correct. Use a hard refresh or close and reopen the
editor before judging the result.

## Rendered markup contract

The editor model, PHP renderer, and frontend JavaScript must agree on the DOM:

- One stable root selector scoped to the widget instance.
- One navigation/control container when the repeater controls navigation items.
- One nested-children container matching
  `get_default_children_placeholder_selector()`.
- A reliable one-to-one relationship between navigation items and child panels.
- Stable accessible IDs and ARIA relationships where tabs, panels, accordions, or
  switchers are implemented.

Frontend code must always initialize per widget scope. Never query every matching
widget on the page and share mutable state between instances.

## Required behavior tests

Test all of the following before reporting a nested widget complete:

### Elementor editor

- Insert a new widget.
- Confirm all default child containers appear in Navigator.
- Click **Add Item** several times; no console exception may occur.
- Duplicate, remove, and reorder items.
- Confirm repeater rows and nested child containers remain paired.
- Add normal Elementor widgets inside every child container.
- Edit nested content without the sidebar losing focus or resetting unexpectedly.
- Save, reload, and reopen the editor.
- Copy/paste and duplicate the complete nested widget.
- Test responsive mode and Navigator selection.

### Frontend

- Confirm the initial active panel is correct.
- Test mouse, touch, and keyboard controls.
- Test multiple instances on one page.
- Test page reload, responsive breakpoints, hidden tabs/accordions, and delayed image
  loading.
- Verify inactive content follows the intended accessibility behavior and cannot
  receive unintended keyboard focus.
- Confirm no PHP warning, JavaScript error, layout jump, or horizontal overflow.

### Compatibility states

- Free plugin alone.
- Free plus Pro.
- Elementor editor and normal frontend.
- Existing saved widget after plugin update.
- Reduced-motion mode where animation is involved.

## Diagnosing `Cannot read ... defaults`

When the nested repeater throws an error mentioning `defaults`, check these items in
this order:

1. Does the PHP class extend `Widget_Nested_Base`?
2. Does the control use `Control_Nested_Repeater::CONTROL_TYPE`?
3. Does `get_initial_config()` merge the parent configuration?
4. Is there an editor JavaScript registration for the exact widget name?
5. Does the JavaScript class extend `NestedElementBase` and return `NestedView`?
6. Is the editor script actually enqueued with `nested-elements` as a dependency?
7. Was registration completed before the document models were instantiated?
8. Was the editor hard-refreshed after the registration was introduced?

Useful console checks during development:

```js
elementor.widgetsCache[ 'apexadfo-example-nested-widget' ]
elementor.elementsManager.elementTypes[ 'apexadfo-example-nested-widget' ]
```

The first should contain nested configuration such as `defaults`; the second should
be an instance registered from `NestedElementBase`. Having only the first is not
enough.

Do not ship temporary diagnostic scripts, console dumps, or code that reads hard
deprecated Elementor globals. Remove debugging assets after the cause is confirmed.

## Common mistakes to avoid

- Copying only the PHP nested repeater and omitting editor element registration.
- Using different widget names in PHP and JavaScript.
- Enqueuing the editor registration as a frontend widget dependency.
- Assuming visible child containers prove the model is a real nested model.
- Hardcoding child heights, widths, or content structure that should remain under
  Elementor control.
- Replacing `get_initial_config()` instead of merging the parent configuration.
- Allowing repeater rows and child elements to become different lengths or orders.
- Binding global events without namespacing and cleanup on Elementor re-render.
- Initializing multiple times when Elementor rerenders a widget.
- depending permanently on undocumented or deprecated editor globals when a current
  cache/API is available.

## WordPress.org and project compliance

Every nested widget change must also follow the repository-level
`WORDPRESS-ORG-REVIEW-CHECKLIST.md`:

- Decide Free/Pro ownership before implementation.
- Keep all Pro-only implementation physically outside the Free package.
- Prefix PHP declarations, JavaScript globals, handles, hooks, and stored data.
- Register/enqueue JavaScript and CSS through WordPress APIs.
- Sanitize input, validate nonces/capabilities for mutations, and escape output at
  the final output point.
- Use `wp_json_encode()` in PHP.
- Run PHP and JavaScript syntax checks plus the mandatory Plugin Review, security,
  prefix, runtime, and package checks relevant to the change.

## Reusable completion checklist

- [ ] PHP class extends `Widget_Nested_Base`.
- [ ] Nested repeater uses `Control_Nested_Repeater::CONTROL_TYPE`.
- [ ] Default repeater rows and child containers match exactly.
- [ ] Placeholder and target selectors exist in rendered markup.
- [ ] `get_initial_config()` merges the parent config.
- [ ] Editor-only `NestedElementBase` registration exists.
- [ ] PHP `get_name()` and JavaScript `getType()` match exactly.
- [ ] Registration returns `NestedView` and handles delayed API availability.
- [ ] Script uses a prefixed handle and depends on `nested-elements`.
- [ ] Add, duplicate, delete, sort, edit, save, reload, copy, and paste work.
- [ ] Multiple frontend instances and responsive behavior work.
- [ ] Keyboard and accessibility behavior work.
- [ ] Event listeners and observers are destroyed on Elementor rerender.
- [ ] No console errors, PHP warnings, or deprecated-API warnings remain.
- [ ] WordPress.org checklist and required automated checks pass.
- [ ] Temporary debugger files and development output are removed.


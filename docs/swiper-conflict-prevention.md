# Elementor Swiper Conflict Prevention Guidelines

## Overview
When building custom Elementor widgets that use Swiper slider (or any similar carousel library), collisions can occur if standard Swiper classes (`swiper-container`, `swiper-wrapper`, `swiper-slide`) are left in the HTML markup. Elementor's global frontend script runs on the page and automatically initializes Swiper on any element containing these classes, resulting in **double-initialization conflicts** (which breaks layout calculations, leaves slides blank/white, and collapses structural elements).

---

## Prevention Strategy: Complete DOM Class Isolation

To guarantee that Elementor Pro and other third-party Swiper handlers never target or corrupt custom widgets, you must completely isolate the HTML markup, CSS stylesheets, and JS configuration.

### 1. HTML Markup (PHP Widget Template)
Remove all default library class names and replace them with unique, namespace-prefixed classes (e.g. `eas-` prefix):

* **Container Class**: Change `.swiper-container` / `.swiper` to `.eas-nested-slider-swiper`.
* **Wrapper Class**: Change `.swiper-wrapper` to `.eas-slider-slides`.
* **Slide Item Class**: Change `.swiper-slide` to `.eas-slider-slide-item`.

```php
// WRONG (Will trigger Elementor double-initialization)
<div class="eas-nested-slider-swiper swiper-container swiper">
    <div class="swiper-wrapper eas-slider-slides">
        <div class="swiper-slide eas-slider-slide-item">...</div>
    </div>
</div>

// CORRECT (Perfectly isolated and safe)
<div class="eas-nested-slider-swiper">
    <div class="eas-slider-slides">
        <div class="eas-slider-slide-item">...</div>
    </div>
</div>
```

---

### 2. JavaScript Configuration
Configure Swiper to query and apply state classes using the custom isolated names. This prevents it from falling back to standard class matching.

```javascript
var swiperOptions = {
    // 1. Structural Classes
    wrapperClass: 'eas-slider-slides',
    slideClass: 'eas-slider-slide-item',

    // 2. Active & Duplicate State Classes (Essential for Loop mode)
    slideActiveClass: 'eas-slider-slide-active',
    slideDuplicateClass: 'eas-slider-slide-duplicate',
    slideNextClass: 'eas-slider-slide-next',
    slidePrevClass: 'eas-slider-slide-prev',
    slideDuplicateActiveClass: 'eas-slider-slide-duplicate-active',
    slideDuplicateNextClass: 'eas-slider-slide-duplicate-next',
    slideDuplicatePrevClass: 'eas-slider-slide-duplicate-prev',

    // other swiper parameters (loop, speed, direction, etc.)
};

// Initialize Swiper on our custom isolated container selector
var swiperInstance = new Swiper('.eas-nested-slider-swiper', swiperOptions);
```

---

### 3. CSS Stylesheet Layout Rules
Since standard Swiper structural styles (like flex wrapper layout and slide shrinkage) are no longer automatically applied to our custom classes, they must be declared in our custom widget CSS:

```css
/* Custom Swiper container layout setup */
.eas-nested-slider-swiper {
    width: 100%;
    position: relative;
    overflow: hidden;
}

/* Custom Swiper wrapper layout setup */
.eas-slider-slides {
    display: flex !important;
    flex-wrap: nowrap !important;
    position: relative !important;
    height: 100% !important;
    z-index: 1;
    gap: 0px !important;
}

/* Custom Swiper slide item layout setup */
.eas-slider-slide-item {
    position: relative !important;
    flex-shrink: 0 !important;
    height: 100% !important;
    box-sizing: border-box;
}
```

---

## Core Guidelines for Other Nested Widgets (Tabs, Accordions, etc.)

* **Prefix Everything**: Always use unique, namespace-prefixed classes (e.g. `.eas-tab-item`, `.eas-accordion-header`).
* **Never Copy Elementor Class Names**: Never copy class names like `.elementor-tab-title`, `.elementor-accordion-item`, or `.elementor-menu-item` directly into custom widgets unless you want Elementor's global event listeners to execute double actions on them.
* **Isolate JS Event Binding**: In your scripts, scope all jQuery queries inside the wrapper element of your widget (`$scope.find('.my-selector')`) rather than making global DOM queries (`$('.my-selector')`).

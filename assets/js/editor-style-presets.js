/**
 * Apex Style Presets / Design System Manager - Editor Engine
 * Apex Addons for Elementor
 */

(function ($) {
  "use strict";

  var PresetsManager = {
    init: function () {
      this.data = window.apexadfoStylePresetsData || {};
      this.presets = this.data.presets || {};
      this.bindContextMenuObserver();
      this.initModalUI();
    },

    /**
     * Use MutationObserver to detect when Elementor Context Menu is injected into the DOM
     */
    bindContextMenuObserver: function () {
      var self = this;

      // Observe DOM insertions for .elementor-context-menu
      var observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
          if (mutation.addedNodes && mutation.addedNodes.length) {
            var $menu = $(".elementor-context-menu:visible");
            if ($menu.length && !$menu.find(".elementor-context-menu-list__group-apex").length) {
              self.injectContextMenuItems($menu);
            }
          }
        });
      });

      observer.observe(document.body, { childList: true, subtree: true });

      // Fallback right-click listener
      $(document).on("contextmenu", function () {
        setTimeout(function () {
          var $menu = $(".elementor-context-menu:visible");
          if ($menu.length && !$menu.find(".elementor-context-menu-list__group-apex").length) {
            self.injectContextMenuItems($menu);
          }
        }, 30);
      });
    },

    /**
     * Inject "Save as Apex Preset" and "Apply Apex Preset" into Elementor Context Menu
     */
    injectContextMenuItems: function ($menu) {
      var self = this;
      if (!$menu || !$menu.length || $menu.find(".elementor-context-menu-list__group-apex").length) {
        return;
      }

      var $apexGroup = $(
        '<div class="elementor-context-menu-list__group elementor-context-menu-list__group-apex" role="group">' +
          '<div class="elementor-context-menu-list__item elementor-context-menu-list__item-apex-save" role="menuitem" tabindex="0">' +
            '<div class="elementor-context-menu-list__item__title">Save as Apex Preset</div>' +
          '</div>' +
          '<div class="elementor-context-menu-list__item elementor-context-menu-list__item-apex-apply" role="menuitem" tabindex="0">' +
            '<div class="elementor-context-menu-list__item__title">Apply Apex Preset</div>' +
          '</div>' +
        '</div>'
      );

      var $saveGroup = $menu.find(".elementor-context-menu-list__group-save");
      var $clipboardGroup = $menu.find(".elementor-context-menu-list__group-clipboard");

      if ($saveGroup.length) {
        $saveGroup.after($apexGroup);
      } else if ($clipboardGroup.length) {
        $clipboardGroup.after($apexGroup);
      } else {
        $menu.find(".elementor-context-menu-list").append($apexGroup);
      }

      // Bind Click Handlers
      $apexGroup.find(".elementor-context-menu-list__item-apex-save").on("click", function (e) {
        e.stopPropagation();
        $(".elementor-context-menu").hide();
        self.openSaveModal();
      });

      $apexGroup.find(".elementor-context-menu-list__item-apex-apply").on("click", function (e) {
        e.stopPropagation();
        $(".elementor-context-menu").hide();
        self.openApplyModal();
      });
    },

    /**
     * Get currently selected Elementor view & model
     */
    getSelectedElement: function () {
      if (window.elementor && window.elementor.selection) {
        var elements = window.elementor.selection.getElements();
        if (elements && elements.length > 0) {
          return elements[0];
        }
      }
      return null;
    },

    /**
     * Extract settings from Elementor Model for preset saving
     */
    extractModelSettings: function (model, options) {
      options = options || { padding: true, background: true, border: true };
      var settings = model.get("settings").toJSON();
      var captured = {};

      // Padding & Margin keys across all viewports (Desktop, Tablet, Mobile)
      if (options.padding) {
        var layoutKeys = [
          "padding", "padding_tablet", "padding_mobile",
          "margin", "margin_tablet", "margin_mobile",
          "padding_unit", "margin_unit",
          "align_items", "align_items_tablet", "align_items_mobile",
          "justify_content", "justify_content_tablet", "justify_content_mobile",
          "flex_direction", "flex_direction_tablet", "flex_direction_mobile",
          "gap", "gap_tablet", "gap_mobile", "gap_unit"
        ];
        layoutKeys.forEach(function (key) {
          if (settings[key] !== undefined) {
            captured[key] = settings[key];
          }
        });
      }

      // Background keys
      if (options.background) {
        var bgKeys = [
          "background_background", "background_color", "background_color_stop",
          "background_color_b", "background_color_b_stop", "background_gradient_type",
          "background_gradient_angle", "background_position", "background_repeat",
          "background_size", "background_attachment"
        ];
        bgKeys.forEach(function (key) {
          if (settings[key] !== undefined) {
            captured[key] = settings[key];
          }
        });
      }

      // Border & Box Shadow keys
      if (options.border) {
        var borderKeys = [
          "border_border", "border_width", "border_color",
          "border_radius", "border_radius_tablet", "border_radius_mobile",
          "box_shadow_box_shadow", "box_shadow_position"
        ];
        borderKeys.forEach(function (key) {
          if (settings[key] !== undefined) {
            captured[key] = settings[key];
          }
        });
      }

      return captured;
    },

    /**
     * Build and inject Modal UI structure into DOM
     */
    initModalUI: function () {
      if ($("#apex-preset-modal-wrap").length) return;

      var modalHtml =
        '<div id="apex-preset-modal-wrap">' +
        '  <div class="apex-preset-modal-backdrop" id="apex-preset-modal-backdrop">' +
        '    <div class="apex-preset-modal">' +
        '      <div class="apex-preset-modal-header">' +
        '        <h3 class="apex-preset-modal-title" id="apex-preset-modal-header-title">Apex Style Preset</h3>' +
        '        <button class="apex-preset-modal-close" id="apex-preset-modal-close">&times;</button>' +
        '      </div>' +
        '      <div class="apex-preset-modal-body" id="apex-preset-modal-body"></div>' +
        '      <div class="apex-preset-modal-footer" id="apex-preset-modal-footer"></div>' +
        '    </div>' +
        '  </div>' +
        '  <div class="apex-preset-toast" id="apex-preset-toast"></div>' +
        '</div>';

      $("body").append(modalHtml);

      $("#apex-preset-modal-close, #apex-preset-modal-backdrop").on("click", function (e) {
        if (e.target === this) {
          PresetsManager.closeModal();
        }
      });
    },

    openModal: function () {
      $("#apex-preset-modal-backdrop").addClass("apex-modal-active");
    },

    closeModal: function () {
      $("#apex-preset-modal-backdrop").removeClass("apex-modal-active");
    },

    showToast: function (msg, type) {
      type = type || "success";
      var $toast = $("#apex-preset-toast");

      var iconSvg = type === "error"
        ? '<span class="apex-preset-toast-icon"><svg viewBox="0 0 24 24"><path fill="#ef4444" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg></span>'
        : '<span class="apex-preset-toast-icon"><svg viewBox="0 0 24 24"><path fill="#10b981" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg></span>';

      $toast
        .removeClass("apex-toast-success apex-toast-error")
        .addClass("apex-toast-" + type)
        .html(iconSvg + "<span>" + this.escapeHtml(msg) + "</span>")
        .addClass("apex-toast-active");

      setTimeout(function () {
        $toast.removeClass("apex-toast-active");
      }, 3200);
    },

    /**
     * Modal View: Save Preset (Clean White Input & Black Text)
     */
    openSaveModal: function () {
      var self = this;
      var activeEl = this.getSelectedElement();
      if (!activeEl) {
        this.showToast("Please select a container or widget first.", "error");
        return;
      }

      var model = activeEl.model || (activeEl.getContainer ? activeEl.getContainer().model : null);
      if (!model) {
        this.showToast("Could not locate element model.", "error");
        return;
      }

      var elType = model.get("elType") || "element";
      var widgetType = model.get("widgetType") || elType;

      var bodyHtml =
        '<div class="apex-preset-field-group" style="margin-bottom: 0;">' +
        '  <label class="apex-preset-label">Preset Name</label>' +
        '  <input type="text" id="apex-preset-name-input" class="apex-preset-input" placeholder="e.g. Hero Section (120px Padding)" value="" style="background-color: #ffffff !important; color: #000000 !important; border: 1px solid #cbd5e1 !important;" />' +
        '</div>';

      var footerHtml =
        '<button class="apex-preset-btn-secondary" id="apex-preset-cancel-btn">Cancel</button>' +
        '<button class="apex-preset-btn-primary" id="apex-preset-save-confirm-btn">Save Preset</button>';

      $("#apex-preset-modal-header-title").text("Save as Apex Preset");
      $("#apex-preset-modal-body").html(bodyHtml);
      $("#apex-preset-modal-footer").html(footerHtml);

      this.openModal();
      setTimeout(function () {
        $("#apex-preset-name-input").focus();
      }, 150);

      $("#apex-preset-cancel-btn").on("click", function () {
        self.closeModal();
      });

      $("#apex-preset-save-confirm-btn").on("click", function () {
        var name = $("#apex-preset-name-input").val().trim();
        if (!name) {
          $("#apex-preset-name-input").css("border-color", "#ef4444").focus();
          return;
        }

        // Save all styling options by default
        var opts = { padding: true, background: true, border: true };
        var settings = self.extractModelSettings(model, opts);

        if ($.isEmptyObject(settings)) {
          self.showToast("No settings captured from selected element.", "error");
          return;
        }

        self.savePresetAJAX({
          title: name,
          target_type: elType,
          element_name: widgetType,
          settings: settings,
        });
      });
    },

    /**
     * Send Save Preset AJAX Request
     */
    savePresetAJAX: function (payload) {
      var self = this;
      var postData = {
        action: "apexadfo_save_style_preset",
        security: self.data.nonce,
        title: payload.title,
        target_type: payload.target_type,
        element_name: payload.element_name,
        settings_json: JSON.stringify(payload.settings),
      };

      $.post(self.data.ajax_url, postData, function (response) {
        if (response && response.success) {
          self.presets = response.data.presets || self.presets;
          self.closeModal();
          self.showToast("Preset '" + payload.title + "' saved successfully!");
        } else {
          var errMsg = response && response.data && response.data.message ? response.data.message : "Failed to save preset.";
          self.showToast(errMsg, "error");
        }
      });
    },

    /**
     * Modal View: Apply Preset
     */
    openApplyModal: function () {
      var self = this;
      var activeEl = this.getSelectedElement();
      if (!activeEl) {
        this.showToast("Please select a container or widget first.", "error");
        return;
      }

      var presetKeys = Object.keys(self.presets);
      var bodyHtml = "";

      if (!presetKeys.length) {
        bodyHtml =
          '<div style="text-align: center; padding: 30px 10px; color: #64748b;">' +
          '  <p style="font-size: 15px; font-weight: 600; margin-bottom: 6px; color: #1e293b;">No Presets Saved Yet</p>' +
          '  <p style="font-size: 13px; margin: 0;">Right-click any container or widget and choose <strong>Save as Apex Preset</strong> to create your first preset.</p>' +
          '</div>';
      } else {
        bodyHtml = '<div class="apex-preset-list">';
        presetKeys.forEach(function (id) {
          var item = self.presets[id];
          bodyHtml +=
            '<div class="apex-preset-item" data-id="' + item.id + '">' +
            '  <div class="apex-preset-item-info">' +
            '    <div class="apex-preset-item-name">' + self.escapeHtml(item.title) + "</div>" +
            '    <div class="apex-preset-item-meta">' + self.escapeHtml(item.target_type.toUpperCase()) + " • " + (item.created_at || "Saved") + "</div>" +
            "  </div>" +
            '  <div class="apex-preset-item-actions">' +
            '    <button class="apex-preset-apply-btn" data-id="' + item.id + '">Apply Preset</button>' +
            '    <button class="apex-preset-delete-btn" data-id="' + item.id + '">Delete</button>' +
            "  </div>" +
            "</div>";
        });
        bodyHtml += "</div>";
      }

      var footerHtml = '<button class="apex-preset-btn-secondary" id="apex-preset-apply-close-btn">Close</button>';

      $("#apex-preset-modal-header-title").text("Apply Apex Preset");
      $("#apex-preset-modal-body").html(bodyHtml);
      $("#apex-preset-modal-footer").html(footerHtml);

      this.openModal();

      $("#apex-preset-apply-close-btn").on("click", function () {
        self.closeModal();
      });

      // Bind Apply Buttons
      $(".apex-preset-apply-btn").on("click", function () {
        var presetId = $(this).data("id");
        var preset = self.presets[presetId];
        if (preset) {
          self.applyPresetToModel(activeEl, preset);
        }
      });

      // Bind Delete Buttons
      $(".apex-preset-delete-btn").on("click", function () {
        var presetId = $(this).data("id");
        if (confirm("Are you sure you want to delete this preset?")) {
          self.deletePresetAJAX(presetId);
        }
      });
    },

    /**
     * Apply Preset Settings to Elementor Model & Re-render Canvas
     */
    applyPresetToModel: function (activeEl, preset) {
      if (!activeEl || !preset || !preset.settings) return;

      var model = activeEl.model || (activeEl.getContainer ? activeEl.getContainer().model : null);
      if (!model) {
        this.showToast("Could not locate element model.", "error");
        return;
      }

      var settings = preset.settings;
      var container = activeEl.getContainer ? activeEl.getContainer() : activeEl;

      try {
        // Attempt Elementor Official $e Command API first (Elementor 3.0+)
        if (window.$e && window.$e.run) {
          window.$e.run("document/elements/settings", {
            container: container,
            settings: settings,
            options: { external: true },
          });
        } else {
          // Fallback: Model setting iteration
          var modelSettings = model.get("settings");
          if (modelSettings) {
            Object.keys(settings).forEach(function (key) {
              modelSettings.set(key, settings[key]);
            });
            modelSettings.trigger("change");
          }
          model.trigger("change");
          model.trigger("change:settings");
        }
      } catch (err) {
        console.warn("Apex Presets: $e Command fallback...", err);
        var modelSettings = model.get("settings");
        if (modelSettings) {
          Object.keys(settings).forEach(function (key) {
            modelSettings.set(key, settings[key]);
          });
          modelSettings.trigger("change");
        }
      }

      // Safely trigger view update on canvas without destroying sidebar panel
      try {
        var view = activeEl.render ? activeEl : (container && container.view ? container.view : null);
        if (view && typeof view.render === "function") {
          view.render();
        }
      } catch (e) {}

      this.closeModal();
      this.showToast("Applied preset '" + preset.title + "'!");
    },

    /**
     * Delete Preset via AJAX
     */
    deletePresetAJAX: function (presetId) {
      var self = this;
      var postData = {
        action: "apexadfo_delete_style_preset",
        security: self.data.nonce,
        preset_id: presetId,
      };

      $.post(self.data.ajax_url, postData, function (response) {
        if (response && response.success) {
          delete self.presets[presetId];
          self.showToast("Preset deleted.");
          self.openApplyModal(); // re-render list
        } else {
          self.showToast("Failed to delete preset.", "error");
        }
      });
    },

    escapeHtml: function (str) {
      return String(str || "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;");
    },
  };

  $(window).on("elementor/init", function () {
    PresetsManager.init();
  });

  // Fallback poll initialization
  var initTimer = setInterval(function () {
    if (window.elementor && window.elementor.selection) {
      clearInterval(initTimer);
      PresetsManager.init();
    }
  }, 1000);

})(jQuery);

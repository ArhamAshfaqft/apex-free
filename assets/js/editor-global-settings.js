(function ($) {
  "use strict";

  // Poll for the stable "Design System" button in Elementor's header toolbar immediately
  var checkHeaderTimer = setInterval(function () {
    var $designSystemBtn = $(
      'button[value="Design System"], button[aria-label="Design System"]',
    );
    if ($designSystemBtn.length) {
      clearInterval(checkHeaderTimer);

      // Prevent duplicate injections
      if ($(".eas-global-settings-trigger").length) return;

      // Ingest the Apex favicon and Mui button wrapper matching Elementor's top bar style
      var faviconUrl = (window.apexadfoGlobalSettingsData && window.apexadfoGlobalSettingsData.favicon_url)
        ? window.apexadfoGlobalSettingsData.favicon_url
        : '';

      var iconHtml = faviconUrl
        ? '<img src="' + faviconUrl + '" style="width: 24px; height: 24px; border-radius: 50%; display: block; object-fit: contain;" alt="Apex Addons Settings" />'
        : '<svg style="width: 22px; height: 22px;" viewBox="0 0 32 32"><circle cx="16" cy="16" r="16" fill="#7c3aed"></circle></svg>';

      var $btn = $(
        '<span class="MuiBox-root">' +
          '<button class="MuiButtonBase-root MuiToggleButton-root MuiToggleButton-sizeSmall MuiToggleButton-standard eas-global-settings-trigger" ' +
          'type="button" title="Apex Addons Global Settings" style="background: transparent !important; border: none !important; box-shadow: none !important; padding: 2px; display: inline-flex; align-items: center; justify-content: center;">' +
          iconHtml +
          '</button>' +
          '</span>',
      );

      // Insert our logo button immediately after the Design System button's span wrapper
      $designSystemBtn.closest("span").after($btn);

      // Initialize the slide-out sidebar panel
      initSidebarPanel($btn);
    }
  }, 500);

  function initSidebarPanel($btn) {
    if ($(".eas-custom-sidebar").length) return;

    // Fetch localized default/saved settings data
    var data = window.apexadfoGlobalSettingsData || {};
    var saved = data.settings || {};

    var scrollbarStyling = saved.scrollbar_styling === "yes" ? "checked" : "";
    var scrollbarWidth = saved.scrollbar_width || "10";
    var scrollbarBg = saved.scrollbar_bg || "#1e1e1e";
    var scrollbarThumb = saved.scrollbar_thumb || "#a855f7";
    var smoothScroll = saved.smooth_scroll === "yes" ? "checked" : "";
    var smoothDuration = parseFloat(saved.smooth_duration || "0.8");
    if (isNaN(smoothDuration)) {
      smoothDuration = 0.8;
    }
    smoothDuration = Math.max(0.1, Math.min(3, smoothDuration)).toFixed(1);
    var cursorStyle = saved.cursor_style || "none";
    var cursorColor = saved.cursor_color || "#a855f7";

    // Ingress custom sidebar HTML layout
    var sidebarHTML =
      '<div class="eas-custom-sidebar">' +
      '   <div class="eas-custom-sidebar-header">' +
      "       <h3>Apex Settings</h3>" +
      '       <button class="eas-custom-sidebar-close">&times;</button>' +
      "   </div>" +
      '   <div class="eas-custom-sidebar-body">' +
      // Section 1: Scrollbars
      '       <div class="eas-sidebar-section">' +
      '           <div class="eas-sidebar-section-title">Scrollbar Customizer</div>' +
      '           <div class="eas-sidebar-field eas-sidebar-switch-wrap">' +
      "               <label>Custom Scrollbars</label>" +
      '               <label class="eas-sidebar-switch">' +
      '                   <input type="checkbox" id="eas_sb_toggle" ' +
      scrollbarStyling +
      ">" +
      '                   <span class="eas-sidebar-slider"></span>' +
      "               </label>" +
      "           </div>" +
      '           <div id="eas_sb_fields_wrap" style="display: ' +
      (scrollbarStyling ? "block" : "none") +
      '; margin-top: 10px;">' +
      '               <div class="eas-sidebar-field" style="margin-bottom: 16px;">' +
      "                   <label>Scrollbar Width</label>" +
      '                   <div class="eas-sidebar-slider-wrap">' +
      '                       <input type="range" id="eas_sb_width" min="0" max="24" value="' +
      scrollbarWidth +
      '">' +
      '                       <span class="eas-sidebar-slider-val" id="eas_sb_width_val">' +
      scrollbarWidth +
      "px</span>" +
      "                   </div>" +
      "               </div>" +
      '               <div class="eas-sidebar-field" style="margin-bottom: 16px;">' +
      "                   <label>Track Background Color</label>" +
      '                   <div class="eas-sidebar-color-wrap">' +
      '                       <input type="color" id="eas_sb_bg" value="' +
      scrollbarBg +
      '">' +
      '                       <input type="text" class="eas-sidebar-color-text" id="eas_sb_bg_txt" value="' +
      scrollbarBg +
      '">' +
      "                   </div>" +
      "               </div>" +
      '               <div class="eas-sidebar-field">' +
      "                   <label>Thumb Color</label>" +
      '                   <div class="eas-sidebar-color-wrap">' +
      '                       <input type="color" id="eas_sb_thumb" value="' +
      scrollbarThumb +
      '">' +
      '                       <input type="text" class="eas-sidebar-color-text" id="eas_sb_thumb_txt" value="' +
      scrollbarThumb +
      '">' +
      "                   </div>" +
      "               </div>" +
      "           </div>" +
      "       </div>" +
      // Section 2: Smooth Scroll
      '       <div class="eas-sidebar-section">' +
      '           <div class="eas-sidebar-section-title">Smooth Scroll</div>' +
      '           <div class="eas-sidebar-field eas-sidebar-switch-wrap">' +
      "               <label>Enable Smooth Scroll</label>" +
      '               <label class="eas-sidebar-switch">' +
      '                   <input type="checkbox" id="eas_ss_toggle" ' +
      smoothScroll +
      ">" +
      '                   <span class="eas-sidebar-slider"></span>' +
      "               </label>" +
      "           </div>" +
      '           <div class="eas-sidebar-field" id="eas_ss_lerp_field" style="display: ' +
      (smoothScroll ? "flex" : "none") +
      ';">' +
      "               <label>Motion Smoothing</label>" +
      '               <div class="eas-sidebar-slider-wrap">' +
      '                   <input type="range" id="eas_ss_duration" min="0.1" max="3.0" step="0.1" value="' +
      smoothDuration +
      '">' +
      '                   <span class="eas-sidebar-slider-val" id="eas_ss_duration_val">' +
      smoothDuration +
      "s</span>" +
      "               </div>" +
      '               <small style="display:block;margin-top:6px;color:#8a94a6;font-size:11px;line-height:1.4;">Lower = tighter response. Higher = slower cinematic catch-up.</small>' +
      "           </div>" +
      "       </div>" +
      // Section 3: Cursors
      '       <div class="eas-sidebar-section">' +
      '           <div class="eas-sidebar-section-title">Creative Cursors</div>' +
      '           <div class="eas-sidebar-field">' +
      "               <label>Cursor Effect Style</label>" +
      '               <select class="eas-sidebar-select" id="eas_cur_style">' +
      '                   <option value="none" ' +
      (cursorStyle === "none" ? "selected" : "") +
      ">Disabled (Native)</option>" +
      '                   <option value="pointer" ' +
      (cursorStyle === "pointer" ? "selected" : "") +
      ">Spring Follower</option>" +
      '                   <option value="difference" ' +
      (cursorStyle === "difference" ? "selected" : "") +
      ">Inverse Color Circle</option>" +
      '                   <option value="ring-dot" ' +
      (cursorStyle === "ring-dot" ? "selected" : "") +
      ">Ring & Dot Follower</option>" +
      '                   <option value="glow-blob" ' +
      (cursorStyle === "glow-blob" ? "selected" : "") +
      ">Ambient Glow Blob</option>" +
      "               </select>" +
      "           </div>" +
      '           <div class="eas-sidebar-field" id="eas_cur_color_field" style="display: ' +
      (cursorStyle !== "none" ? "flex" : "none") +
      ';">' +
      "               <label>Cursor Base Color</label>" +
      '               <div class="eas-sidebar-color-wrap">' +
      '                   <input type="color" id="eas_cur_color" value="' +
      cursorColor +
      '">' +
      '                   <input type="text" class="eas-sidebar-color-text" id="eas_cur_color_txt" value="' +
      cursorColor +
      '">' +
      "               </div>" +
      "           </div>" +
      "       </div>" +
      "   </div>" +
      '   <div class="eas-custom-sidebar-footer">' +
      '       <button class="eas-custom-sidebar-save">' +
      '           <span class="eas-sidebar-loader"></span>' +
      '           <span class="eas-sidebar-save-label">Save Settings</span>' +
      "       </button>" +
      "   </div>" +
      "</div>";

    // Inject sidebar panel into DOM body
    $("body").append(sidebarHTML);

    var $sidebar = $(".eas-custom-sidebar");

    // Toggle sidebar visibility on clicking logo trigger button
    $btn.on("click", function (e) {
      e.preventDefault();
      $sidebar.toggleClass("active");
    });

    // Close sidebar panel
    $(".eas-custom-sidebar-close").on("click", function () {
      $sidebar.removeClass("active");
    });

    // Handle live slider val syncs
    $("#eas_sb_width").on("input", function () {
      $("#eas_sb_width_val").text($(this).val() + "px");
    });

    $("#eas_ss_duration").on("input", function () {
      $("#eas_ss_duration_val").text($(this).val() + "s");
    });

    // Handle live color picker text syncs
    function bindColorSync(pickerId, txtId) {
      $(pickerId).on("input", function () {
        $(txtId).val($(this).val());
      });
      $(txtId).on("input", function () {
        var val = $(this).val();
        if (val.match(/^#[0-9a-fA-F]{6}$/)) {
          $(pickerId).val(val);
        }
      });
    }
    bindColorSync("#eas_sb_bg", "#eas_sb_bg_txt");
    bindColorSync("#eas_sb_thumb", "#eas_sb_thumb_txt");
    bindColorSync("#eas_cur_color", "#eas_cur_color_txt");

    // Handle conditional fields visibility
    $("#eas_sb_toggle").on("change", function () {
      var isChecked = $(this).is(":checked");
      $("#eas_sb_fields_wrap").toggle(isChecked);
    });

    $("#eas_ss_toggle").on("change", function () {
      var isChecked = $(this).is(":checked");
      $("#eas_ss_lerp_field").toggle(isChecked);
    });

    // Handle cursor style toggle visibility
    $("#eas_cur_style").on("change", function () {
      var style = $(this).val();
      $("#eas_cur_color_field").toggle(style !== "none");
    });

    // Save Settings event listener
    $(".eas-custom-sidebar-save").on("click", function () {
      var $saveBtn = $(this);
      var $label = $saveBtn.find(".eas-sidebar-save-label");

      $saveBtn.addClass("saving");
      $label.text("Saving...");

      var settingsPayload = {
        scrollbar_styling: $("#eas_sb_toggle").is(":checked") ? "yes" : "no",
        scrollbar_width: $("#eas_sb_width").val(),
        scrollbar_bg: $("#eas_sb_bg").val(),
        scrollbar_thumb: $("#eas_sb_thumb").val(),
        smooth_scroll: $("#eas_ss_toggle").is(":checked") ? "yes" : "no",
        smooth_duration: $("#eas_ss_duration").val(),
        cursor_style: $("#eas_cur_style").val(),
        cursor_color: $("#eas_cur_color").val(),
      };

      $.ajax({
        url: data.ajax_url,
        type: "POST",
        data: {
          action: "apexadfo_save_global_settings",
          nonce: data.nonce,
          settings: settingsPayload,
        },
        success: function (response) {
          $saveBtn.removeClass("saving").addClass("success");
          $label.text("Saved Successfully!");

          // Reset button state feedback after a short duration
          setTimeout(function () {
            $saveBtn.removeClass("success");
            $label.text("Save Settings");
          }, 2000);
        },
        error: function () {
          $saveBtn.removeClass("saving");
          $label.text("Error Saving");
          setTimeout(function () {
            $label.text("Save Settings");
          }, 2000);
        },
      });
    });
  }
})(jQuery);

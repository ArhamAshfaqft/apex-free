(function () {
  "use strict";
  var globalConfig = window.apexadfoQuizConfig || { ajaxurl: "", i18n: {} };
  function el(tag, className, text) {
    var node = document.createElement(tag);
    if (className) node.className = className;
    if (text != null) node.textContent = text;
    return node;
  }

  function Quiz(root) {
    this.root = root;
    this.panel = root.querySelector(".eas-quiz-panel");
    this.stage = root.querySelector(".eas-quiz-stage");
    this.error = root.querySelector(".eas-quiz-error");
    this.progress = root.querySelector(".eas-quiz-progress");
    this.counter = root.querySelector(".eas-quiz-counter");
    this.back = root.querySelector(".eas-quiz-back");
    this.restart = root.querySelector(".eas-quiz-restart");
    this.config = JSON.parse(root.getAttribute("data-eas-quiz-config") || "{}");
    this.steps = this.config.steps || [];
    this.index = 0;
    this.history = [];
    this.answers = {};
    this.busy = false;
    this.isEditor = !!(
      window.elementorFrontend &&
      typeof elementorFrontend.isEditMode === "function" &&
      elementorFrontend.isEditMode()
    );
    window.easQuizEditorState = window.easQuizEditorState || {};
    this.stateKey = root.id;
    var saved = this.isEditor ? window.easQuizEditorState[this.stateKey] : null;
    if (saved) {
      this.index = Math.min(
        this.steps.length - 1,
        Math.max(0, saved.index || 0),
      );
      this.history = saved.history || [];
      this.answers = saved.answers || {};
    }
    this.abortController =
      typeof AbortController !== "undefined" ? new AbortController() : null;
    this.bind();
    this.render();
  }
  Quiz.prototype.destroy = function () {
    if (this.abortController) this.abortController.abort();
  };
  Quiz.prototype.bind = function () {
    var self = this;
    var options = this.abortController
      ? { signal: this.abortController.signal }
      : false;
    this.back.addEventListener(
      "click",
      function () {
        self.goBack();
      },
      options,
    );
    this.restart.addEventListener(
      "click",
      function () {
        self.reset();
      },
      options,
    );
  };
  Quiz.prototype.saveState = function () {
    if (this.isEditor)
      window.easQuizEditorState[this.stateKey] = {
        index: this.index,
        history: this.history.slice(),
        answers: Object.assign({}, this.answers),
      };
  };
  Quiz.prototype.setError = function (message) {
    this.error.textContent = message || "";
    this.error.hidden = !message;
  };
  Quiz.prototype.current = function () {
    return this.steps[this.index] || null;
  };
  Quiz.prototype.isResultStep = function (step) {
    return !!(
      step &&
      (step.items || []).some(function (item) {
        return item.type === "result";
      })
    );
  };

  Quiz.prototype.isQuestionStep = function (step) {
    return !!(
      step &&
      (step.items || []).some(function (item) {
        return ["single", "multiple", "text", "email"].indexOf(item.type) !== -1;
      })
    );
  };

  Quiz.prototype.render = function () {
    var self = this,
      step = this.current();
    if (!step) return;
    this.saveState();
    this.setError("");
    this.stage.replaceChildren();
    var screen = el("div", "eas-quiz-screen");
    var grid = el("div", "eas-quiz-grid");
    (step.items || []).forEach(function (item) {
      self.renderItem(grid, item);
    });
    screen.appendChild(grid);
    this.stage.appendChild(screen);

    var questionSteps = this.steps.filter(this.isQuestionStep.bind(this));
    var currentQuestionIndex = questionSteps.indexOf(step);

    var showProgress = !!this.config.showProgress;
    this.progress.hidden = !showProgress;
    this.progress.style.display = showProgress ? "" : "none";
    if (showProgress && this.progress.querySelector("span")) {
      var percent = 0;
      if (this.isResultStep(step)) {
        percent = 100;
      } else if (questionSteps.length > 0 && currentQuestionIndex !== -1) {
        percent = Math.round(((currentQuestionIndex + 1) / questionSteps.length) * 100);
      }
      this.progress.querySelector("span").style.width = percent + "%";
    }

    var showCounter = !!(
      this.config.showCounter &&
      currentQuestionIndex !== -1 &&
      !this.isResultStep(step)
    );
    this.counter.hidden = !showCounter;
    this.counter.style.display = showCounter ? "" : "none";
    this.counter.textContent = showCounter
      ? "Step " + (currentQuestionIndex + 1) + " of " + questionSteps.length
      : "";

    var showBack = !!(
      this.config.allowBack &&
      !this.isResultStep(step) &&
      (this.history.length > 0 || (this.isEditor && this.config.allowBack))
    );
    this.back.textContent = this.config.labels.back || "Back";
    this.back.hidden = !showBack;
    this.back.style.display = showBack ? "" : "none";
    this.back.disabled = !this.history.length;

    var showRestart = !!this.config.allowRestart;
    this.restart.textContent = this.config.labels.restart || "Restart quiz";
    this.restart.hidden = !showRestart;
    this.restart.style.display = showRestart ? "" : "none";
    window.requestAnimationFrame(function () {
      screen.classList.add("is-active");
      if (!self.isEditor) {
        var heading = screen.querySelector(".eas-quiz-heading");
        if (heading) {
          heading.tabIndex = -1;
          heading.focus({ preventScroll: true });
        }
      }
    });
  };

  Quiz.prototype.renderItem = function (grid, item) {
    var self = this,
      wrap = el("div", "eas-quiz-item eas-quiz-item-" + item.type);
    wrap.style.setProperty("--quiz-width", (item.width || 100) + "%");
    wrap.style.setProperty(
      "--quiz-width-tablet",
      (item.width_tablet || 100) + "%",
    );
    wrap.style.setProperty(
      "--quiz-width-mobile",
      (item.width_mobile || 100) + "%",
    );
    if (item.type === "heading") {
      wrap.appendChild(
        el(
          /^(h[1-6]|div)$/.test(item.tag) ? item.tag : "h3",
          "eas-quiz-heading",
          item.content,
        ),
      );
      grid.appendChild(wrap);
      return;
    }
    if (item.type === "description") {
      wrap.appendChild(el("div", "eas-quiz-description", item.content));
      grid.appendChild(wrap);
      return;
    }
    if (item.type === "result") {
      if (this.isEditor) {
        wrap.classList.add("eas-quiz-result");
        wrap.appendChild(el("div", "eas-quiz-result-icon", "✓"));
        wrap.appendChild(
          el(
            "h3",
            "eas-quiz-heading",
            (this.config.defaultResult || {}).title || "Quiz complete",
          ),
        );
        wrap.appendChild(
          el(
            "div",
            "eas-quiz-description",
            (this.config.defaultResult || {}).description || "",
          ),
        );
        if (this.config.showScore)
          wrap.appendChild(el("div", "eas-quiz-score", "0 / 0"));
      }
      grid.appendChild(wrap);
      return;
    }
    if (item.type === "button") {
      var row = el("div", "eas-quiz-action");
      var button = el(
        "button",
        "eas-quiz-button",
        item.button_label || this.config.labels.submit,
      );
      button.type = "button";
      button.addEventListener("click", function () {
        self.advance();
      });
      row.appendChild(button);
      wrap.appendChild(row);
      grid.appendChild(wrap);
      return;
    }
    var label = el("div", "eas-quiz-question-label", item.label || "");
    label.id = this.root.id + "-" + item.id + "-label";
    if (item.required) label.appendChild(el("span", "eas-quiz-required", " *"));
    wrap.appendChild(label);
    if (item.type === "single" || item.type === "multiple") {
      var options = el("div", "eas-quiz-options");
      options.setAttribute("aria-labelledby", label.id);
      if (item.type === "single") options.setAttribute("role", "radiogroup");
      var existing = this.answers[item.id];
      (item.options || []).forEach(function (choice, choiceIndex) {
        var option = el(
          "label",
          "eas-quiz-option eas-quiz-option-" + item.type,
        );
        var input = el("input");
        input.type = item.type === "single" ? "radio" : "checkbox";
        input.name = self.root.id + "-" + item.id;
        input.value = choice.value;
        input.checked =
          item.type === "multiple"
            ? Array.isArray(existing) && existing.indexOf(choice.value) !== -1
            : existing === choice.value;
        var indicator = el("span", "eas-quiz-indicator");
        var text = el("span", "eas-quiz-option-text", choice.label);
        option.appendChild(input);
        option.appendChild(indicator);
        option.appendChild(text);
        option.classList.toggle("is-selected", input.checked);
        input.addEventListener("change", function () {
          if (item.type === "single") {
            self.answers[item.id] = choice.value;
            options.querySelectorAll("input[type='radio']").forEach(function (radio) {
              radio.checked = (radio.value === choice.value);
            });
            options
              .querySelectorAll(".eas-quiz-option")
              .forEach(function (node) {
                node.classList.remove("is-selected");
              });
            option.classList.add("is-selected");
          } else {
            var values = Array.isArray(self.answers[item.id])
              ? self.answers[item.id].slice()
              : [];
            var position = values.indexOf(choice.value);
            if (input.checked && position === -1) values.push(choice.value);
            if (!input.checked && position !== -1) values.splice(position, 1);
            self.answers[item.id] = values;
            option.classList.toggle("is-selected", input.checked);
          }
          self.saveState();
        });
        options.appendChild(option);
      });
      wrap.appendChild(options);
    } else {
      var input = el("input", "eas-quiz-input");
      input.type = item.type === "email" ? "email" : "text";
      input.placeholder = item.placeholder || "";
      input.value = this.answers[item.id] || "";
      input.addEventListener("input", function () {
        self.answers[item.id] = input.value;
        self.saveState();
      });
      input.addEventListener("keydown", function (event) {
        if (event.key === "Enter") {
          event.preventDefault();
          self.advance();
        }
      });
      wrap.appendChild(input);
    }
    grid.appendChild(wrap);
  };

  Quiz.prototype.valid = function () {
    var step = this.current(),
      self = this,
      okay = true;
    (step.items || []).some(function (item) {
      if (["single", "multiple", "text", "email"].indexOf(item.type) === -1)
        return false;
      var value = self.answers[item.id];
      var empty =
        value == null ||
        value === "" ||
        (Array.isArray(value) && !value.length);
      if (item.required && empty) {
        self.setError((item.label || "This question") + " is required.");
        okay = false;
        return true;
      }
      if (!empty && item.type === "email" && !/^\S+@\S+\.\S+$/.test(value)) {
        self.setError(
          globalConfig.i18n.email || "Please enter a valid email address.",
        );
        okay = false;
        return true;
      }
      return false;
    });
    return okay;
  };
  Quiz.prototype.advance = function () {
    if (this.busy || !this.valid()) return;
    var nextIndex = this.index + 1;
    if (
      nextIndex >= this.steps.length ||
      this.isResultStep(this.steps[nextIndex])
    ) {
      if (
        this.config.leadGate &&
        this.config.leadGate.active &&
        !this.answers.__gate_email
      )
        this.renderGate();
      else this.submit();
      return;
    }
    this.history.push(this.index);
    this.index = nextIndex;
    this.render();
  };
  Quiz.prototype.goBack = function () {
    if (!this.history.length || this.busy) return;
    this.index = this.history.pop();
    this.render();
  };
  Quiz.prototype.reset = function () {
    if (this.busy) return;
    this.index = 0;
    this.history = [];
    this.answers = {};
    this.render();
  };
  Quiz.prototype.renderGate = function () {
    var self = this;
    this.stage.replaceChildren();
    var screen = el("div", "eas-quiz-screen is-active eas-quiz-gate");
    screen.appendChild(
      el("h3", "eas-quiz-heading", this.config.leadGate.title),
    );
    var name = el("input", "eas-quiz-input");
    name.placeholder = "Name";
    var email = el("input", "eas-quiz-input");
    email.type = "email";
    email.placeholder = "Email address";
    var button = el("button", "eas-quiz-button", this.config.labels.submit);
    button.type = "button";
    button.addEventListener("click", function () {
      if (!/^\S+@\S+\.\S+$/.test(email.value)) {
        self.setError(
          globalConfig.i18n.email || "Please enter a valid email address.",
        );
        return;
      }
      self.answers.__gate_name = name.value;
      self.answers.__gate_email = email.value;
      self.submit();
    });
    screen.appendChild(name);
    screen.appendChild(email);
    screen.appendChild(button);
    this.stage.appendChild(screen);
  };
  Quiz.prototype.submit = function () {
    var self = this;
    this.busy = true;
    this.setError("");
    var button = this.stage.querySelector(".eas-quiz-button");
    if (button) {
      button.disabled = true;
      button.textContent = globalConfig.i18n.sending || "Calculating…";
    }
    var data = new FormData();
    data.append("action", "apexadfo_quiz_submit");
    data.append("nonce", this.config.nonce);
    data.append("page_id", this.config.pageId);
    data.append("widget_id", this.config.widgetId);
    data.append("answers", JSON.stringify(this.answers));
    if (this.config) {
      data.append("quiz_config", JSON.stringify(this.config));
    }
    fetch(globalConfig.ajaxurl, {
      method: "POST",
      credentials: "same-origin",
      body: data,
    })
      .then(function (response) {
        return response.json().then(function (json) {
          if (!response.ok || !json.success)
            throw new Error(
              (json.data && json.data.message) ||
                globalConfig.i18n.error ||
                "Unable to calculate the result.",
            );
          return json.data;
        });
      })
      .then(function (data) {
        self.renderResult(data);
      })
      .catch(function (error) {
        self.setError(error.message);
        if (button) {
          button.disabled = false;
          button.textContent = self.config.labels.submit;
        }
      })
      .finally(function () {
        self.busy = false;
      });
  };
  Quiz.prototype.renderResult = function (data) {
    this.history.push(this.index);
    var resultIndex = this.steps.findIndex(this.isResultStep.bind(this));
    if (resultIndex !== -1) this.index = resultIndex;
    this.saveState();
    this.stage.replaceChildren();
    var screen = el("div", "eas-quiz-screen is-active eas-quiz-result");
    screen.appendChild(el("div", "eas-quiz-result-icon", "✓"));
    screen.appendChild(el("h3", "eas-quiz-heading", data.result.title));
    screen.appendChild(
      el("div", "eas-quiz-description", data.result.description),
    );
    if (this.config.showScore)
      screen.appendChild(
        el("div", "eas-quiz-score", data.score + " / " + data.maxScore),
      );
    this.stage.appendChild(screen);
    this.progress.querySelector("span").style.width = "100%";
    this.counter.hidden = true;
    this.back.hidden = true;
    this.restart.hidden = !this.config.allowRestart;
  };

  function initialize(scope) {
    (scope || document)
      .querySelectorAll(".eas-quiz-builder")
      .forEach(function (root) {
        var signature = root.getAttribute("data-eas-quiz-config") || "";
        var editor = !!(
          window.elementorFrontend &&
          typeof elementorFrontend.isEditMode === "function" &&
          elementorFrontend.isEditMode()
        );
        if (
          root.dataset.easQuizReady === "true" &&
          (!editor || root.dataset.easQuizSignature === signature)
        )
          return;
        root.dataset.easQuizReady = "true";
        root.dataset.easQuizSignature = signature;
        try {
          if (root.easQuizInstance) root.easQuizInstance.destroy();
          root.easQuizInstance = new Quiz(root);
        } catch (error) {
          delete root.dataset.easQuizReady;
          if (window.console) console.error("Apex Quiz:", error);
        }
      });
  }
  if (document.readyState === "loading")
    document.addEventListener("DOMContentLoaded", function () {
      initialize(document);
    });
  else initialize(document);
  window.addEventListener("elementor/frontend/init", function () {
    if (window.elementorFrontend && elementorFrontend.hooks)
      elementorFrontend.hooks.addAction(
        "frontend/element_ready/eas-quiz-builder.default",
        function ($scope) {
          initialize($scope[0]);
        },
      );
  });
})();

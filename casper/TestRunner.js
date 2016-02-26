var require = patchRequire(require);
var Project = require('Project');

var thrownewError = function(s) {
  console.log('ERROR: ' + s);
  throw new Error(s);
};

/**
 * available options:
 *  testName - файл {testName}.json должен находиться в одной из базовых Ngn директорий в подпапке casper/test
 */
module.exports = new Class({
  Extends: Project,

  logLevel: 2,
  i: 0,
  callbackPrefixes: ['then', 'wait', 'submit'],

  options: {
    captureFolder: null,
    casperOptions: {
      colorizerType: 'Dummy'
    }
  },

  beforeRun: function() {
    this.initSteps();
    this.preParseSteps();
  },

  run: function() {
    this.runStep();
  },

  initCasper: function() {
    this.parent();
    // -- if (this.options.extension) this.casper = Object.merge(this.casper, require(this.options.extension));
    this.casper.on('page.error', function(msg, trace) {
      var t = '';
      for (var i = 0; i < trace.length; i++) {
        t += trace[i].file + ':' + trace[i].line + "\n";
      }
      console.debug(msg + "\n" + t);
      this.exit(1);
    });
    this.casper.on('remote.message', function(message) {
      console.debug('CLIENT: ' + message);
    });
    this.casper.setFilter('page.confirm', function(msg) {
      return true;
    });
    var runner = this;
    this.casper.thenUrl = function(url, callback) {
      console.log('Open url "' + runner.baseUrl + '/' + url + '"');
      this.thenOpen(runner.baseUrl + '/' + url, function() {
        this.evaluate(function() {
        });
        this.wait(100, callback);
      });
    };
    this.casper.waitForRequest = function(callback) {
      this.waitFor(function() {
        var length = this.evaluate(function() {
          return Ngn.Request.inProgress.length;
        });
        return length === 0;
      }, callback);
    };
    this.casper.submitDialog = function(callback) {
      this.click('.dialog-footer .ok a');
      this.waitForRequest(function() {
        this.wait(50, callback);
      });
    };
    this.casper.closeDialog = function(callback) {
      this.click('.md-closer');
      this.waitForDialogClose(callback);
    };
    this.casper.waitForDialog = function(callback) {
      this.waitForSelector('.dialog .apeform', function() {
        this.evaluate(function() {
        });
        callback();
      });
    };
    this.casper.waitForDialogClose = function(callback) {
      this.waitWhileSelector('.dialog .apeform', function() {
        this.evaluate(function() {
        });
        callback();
      });
    };
    this.casper.selectOption = function(selector, value) {
      this.evaluate(function(selector, value) {
        document.querySelector(selector).value = value;
      }, {
        selector: selector,
        value: value
      });
    };
    this.casper.checkExistence = function(selector) {
      return [this.exists(selector), //
        '"' + selector + '" selector does not exists', //
        '"' + selector + '" selector exists'];
    };
    this.casper.checkText = function(selector, textToCompare) {
      var text = this.fetchText(selector).trim();
      return [text == textToCompare, //
        'text and textToCompare are not identical. Selector "' + selector + '" value: ' + text + '; text to compare: ' + textToCompare, 'text and textToCompare are identical. Selector "' + selector + '" value: ' + text + '; text to compare: ' + textToCompare];
    };
    this.casper.printText = function(selector) {
      console.debug(this.fetchText(selector));
    };
    this.casper.printProperty = function(selector, property) {
      var value = this.evaluate(function(selector, property) {
        return document.querySelector(selector)[property];
      }, {
        selector: selector,
        property: property
      });
      console.log(selector + ' [' + property + '] = "' + value + '"');
    };
  },

  isCallbackMethod: function(name) {
    for (var i = 0; i < this.callbackPrefixes.length; i++) {
      if (name.substr(0, this.callbackPrefixes[i].length) === this.callbackPrefixes[i]) {
        return true;
      }
    }
    return false;
  },

  processStep: function(step) {
    var methodName = step[0];
    var negativeCheck = false;
    if (methodName.substr(0, 1) == '!') {
      methodName = methodName.substr(1, methodName.length);
      negativeCheck = true;
    }
    var nextMethod, methodBind, method;
    if (this[methodName]) {
      method = this[methodName];
      methodBind = this;
    } else if (this.casper[methodName]) {
      method = this.casper[methodName];
      methodBind = this.casper;
    } else if (this.casper.page[methodName]) {
      method = this.casper.page[methodName];
      methodBind = this.casper;
    } else {
      thrownewError('Casper or TestRunner method "' + methodName + '" is absent');
    }
    var params = null;
    if (this.isCallbackMethod(methodName)) {
      nextMethod = this.getNextMethod();
      params = step[1] !== undefined ? step.slice(1, step.length) : [];
      params.push(nextMethod); // last param is always casper callback function
      method = method.pass(params, methodBind);
      this.callMethod(methodName, method);
    } else {
      if (step[1] !== undefined) {
        params = step.slice(1, step.length);
        method = method.pass(params, methodBind);
      } else {
        method = method.bind(methodBind);
      }
      this.callMethod(methodName, method, negativeCheck);
      this.getNextMethod()();
    }
  },

  callMethod: function(methodName, method, negativeCheck) {
    if (methodName.substr(0, 5) == 'check') {
      var r = method();
      if (r[0] !== !negativeCheck) {
        thrownewError(r[negativeCheck ? 2 : 1]);
      }
      this.log((negativeCheck ? '!' : '') + methodName + ' success');
      return;
    }
    method();
  },

  getNextMethod: function() {
    if (!this.options.steps[this.i + 1]) {
      return function() {
        this.casper.wait(1000, function() {
          this.log('There are no more steps', 2);
        });
      }.bind(this);
    }
    return function() {
      this.nextStep();
    }.bind(this);
  },

  _capture: function(caption) {
    if (this.options.captureFolder) {
      var id = this.makeCapture(caption);
      this.afterCaptureCmd('rumax/save', 'id=' + id + //
        '+folder=' + this.options.captureFolder + //
        '+n=' + (this.i + 1) + //
        '+caption=' + caption.replace(new RegExp(' ', 'g'), '_') //
      );
      return;
    }
    this.capture(caption, this.i + 1);
  },

  runStep: function() {
    if (!this.options.steps[this.i]) thrownewError('next step does not exists');
    var step = this.options.steps[this.i];
    var params = '';
    if (step.length > 1) params = ' (' + step.slice(1, step.length).join(', ') + ')';
    this.log('STEP ' + (this.i) + '>  ' + step[0] + params, 2);
    this.processStep(this.options.steps[this.i]);
  },

  nextStep: function() {
    this.i++;
    this.runStep();
  },

  /**
   * Получает массив с шагами из stdin'а или json-файла
   */
  initSteps: function() {
    if (!this.options.testName) thrownewError('define testName');
    var file = null;
    var _file = this.options.testName + '.json';
    var found = false;
    for (var i = 0; i < this.options.ngnBasePaths.length; i++) {
      var f = this.options.ngnBasePaths[i] + '/casper/test/' + _file;
      if (require('fs').exists(f)) {
        file = f;
        found = true;
        break;
      }
    }
    if (!found) {
      require('utils').dump(this.options.ngnBasePaths);
      thrownewError('File "casper/test/' + _file + '" not found in ngnBasePaths');
    }
    this.log('init steps from "' + file + '"', 2);
    var data = require('fs').read(file, 'utf-8');
    try {
      this.options.steps = JSON.decode(data);
      if (!this.options.steps[0]) thrownewError('JSON contents error in file: ' + file);
    } catch (e) {
      if (e.message.test(/Parse error/)) {
        thrownewError('Parse error in file: ' + file);
      } else {
        throw e;
      }
    }
    this.log('initialized:', 2);
    for (var i = 0; i < this.options.steps.length; i++) {
      this.log('  * ' + this.options.steps[i], 3);
    }
  },

  replaceNgnPath: function(step) {
    if (step && step[2] && this.options.ngnPath) {
      if (typeof step[2] == 'object') {
        for (var j in step[2]) {
          if (step[2][j] instanceof Array) {
            for (var k = 0; k < step[2][j].length; k++) {
              if (step[2][j][k].indexOf('{ngnPath}') >= 0) {
                step[2][j][k] = step[2][j][k].replace('{ngnPath}', this.casper.cli.options.ngnPath);
              }
            }
          } else if (typeof step[2][j] == 'string') {
            if (step[2][j].indexOf('{ngnPath}') >= 0) {
              step[2][j] = step[2][j].replace('{ngnPath}', this.casper.cli.options.ngnPath);
            }
          }
        }
      }
    }
  },

  /**
   * Добавляет "wait" и "capture" шаг после шагов с префиксом "~"
   */
  preParseSteps: function() {
    var steps = [];
    for (var i = 0; i < this.options.steps.length; i++) {
      var step = this.options.steps[i];
      this.replaceNgnPath(step);
      if (step[0].substr(0, 1) == '~') {
        step[0] = step[0].substr(1, step[0].length);
        steps.push(step);
        if (step[0] == 'submit') {
          step[0] = 'click';
          step[1] += ' .type_submit input';
          steps.push(['outputRequestLoaded']);
          steps.push(['waitForRequest']);
        }
        if (!this.options.disableCapture) steps.push(['_capture', 'after ' + step[0]]);
      } else {
        steps.push(step);
      }
    }
    this.options.steps = steps;
    this.log('steps prepeared', 3);
  }

});

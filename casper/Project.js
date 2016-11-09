var require = patchRequire(require);
require('mootools');
var Rumax = require('Rumax');
var c = function(v) {
  require('utils').dump(v);
};
var thrownewError = function(s) {
  console.log('ERROR: ' + s);
  throw new Error(s);
};

module.exports = new Class({
  Implements: [Rumax, Options],

  options: {
    casperOptions: {}
  },

  /**
   * available options:
   *   projectDir
   *   rumaxFolder
   *   disableAfterCaptureCmd
   *   disableCapture
   */
  initialize: function(options) {
    this.log('initializing', 3);
    this.setOptions(options);
    this.casper = require('casper').create(this.options.casperOptions);
    this.options = Object.merge(this.options, this.casper.cli.options);
    if (!this.options.projectsDir) thrownewError('option "projectsDir" is required');
    if (!this.options.projectName) thrownewError('option "projectName" is required');
    var ngnBasePathCst = this.options.ngnBasePaths[0];
    this.options = Object.merge(this.options, require(this.options.projectsDir + '/' + this.options.projectName + '/site/casper/config'));
    this.options.ngnBasePaths.push(ngnBasePathCst);
    this.projectDir = this.options.projectsDir + '/' + this.options.projectName;
    if (!require('fs').exists(this.projectDir)) thrownewError('folder "' + this.projectDir + '" does not exists');
    this.log('init casper', 3);
    this.initCasper();
    this.init();
    if (this.options.baseUrl) {
      this.baseUrl = 'http://' + this.options.baseUrl;
      this.startActions();
    } else {
      if (!this.options.projects[this.options.projectName]) {
        thrownewError('Project "' + this.options.projectName + '" does not exists. Use: run ngn-cst/cmd/update ngn-cst');
      }
      this.domain = this.options.projects[this.options.projectName];
      this.baseUrl = 'http://' + this.domain;
      this.startActions();
    }
  },

  startActions: function() {
    if (this.options.debugKey) {
      console.log('setting up debuKey for ' + this.domain);
      phantom.addCookie({
        name: 'debugKey',
        value: this.options.debugKey,
        domain: this.domain
      });
    }
    this.casper.start(this.baseUrl, function(page) {
      if (page.status != 200) {
        thrownewError('Base URL "' + this.baseUrl + '" not works. Status: ' + page.status);
      }
    }.bind(this));
    this.beforeRun();
    this.run();
    this.casper.run();
  },

  init: function() {},

  initCasper: function() {
    this.casper.options.viewportSize = {
      width: 950,
      height: 500
    };
    this.casper.options.pageSettings = {
      //loadPlugins: false,
      //loadImages: false
    };
  },

  beforeRun: function() {
  },

  run: function() {
    thrownewError('abstract');
  }

});
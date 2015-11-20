var require = patchRequire(require);
require('mootools');
var Rumax = require('Rumax');
var c = function(v) {
  require('utils').dump(v);
};

module.exports = new Class({
  Implements: [Rumax, Options],

  options: {
    casperOptions: {}
  },

  /**
   * available options:
   *   projectDir - директория проекта
   *   rumaxFolder - директория для каптч
   *   disableAfterCaptureCmd - выключает создание капч после каждой команды
   *   disableCapture
   */
  initialize: function(options) {
    this.log('initializing', 3);
    this.setOptions(options);
    this.casper = require('casper').create(this.options.casperOptions);
    this.options = Object.merge(this.options, this.casper.cli.options);
    if (!this.options.projectsDir) throw new Error('option "projectsDir" is required');
    if (!this.options.projectName) throw new Error('option "projectName" is required');
    // Мёрджим с конфигом проекта
    var ngnBasePathCst = this.options.ngnBasePaths[0];
    this.options = Object.merge(this.options, require(this.options.projectsDir + '/' + this.options.projectName + '/site/casper/config'));
    this.options.ngnBasePaths.push(ngnBasePathCst);
    this.projectDir = this.options.projectsDir + '/' + this.options.projectName;
    if (!require('fs').exists(this.projectDir)) throw new Error('folder "' + this.projectDir + '" does not exists');
    this.log('init casper', 3);
    this.initCasper();
    this.init();
    if (this.options.baseUrl) {
      this.baseUrl = 'http://' + this.options.baseUrl;
      this.startActions();
    } else {
      if (!this.options.projects[this.options.projectName]) {
        throw new Error('Project "' + this.options.projectName + '" does not exists');
      }
      var domain = this.options.projects[this.options.projectName];
      this.baseUrl = 'http://' + domain;
      this.startActions();
    }
  },

  startActions: function() {
    phantom.addCookie({
      name: 'debugKey',
      value: 'asd',
      domain: 'test.karantin.majexa.ru'
    });
    this.casper.start(this.baseUrl, function(page) {
      if (page.status != 200) {
        throw new Error('Base URL "' + this.baseUrl + '" not works. Status: ' + page.status);
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
    throw new Error('abstract');
  }

});
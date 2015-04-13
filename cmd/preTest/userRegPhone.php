<?php

Dir::clear('C:/1/captures');
ProjectConfig::replaceConstant('more', 'TESTING', true);
ProjectConfig::updateSubVar('userReg', 'emailEnable', false);
ProjectConfig::updateSubVar('userReg', 'enable', true);
ProjectConfig::updateSubVar('userReg', 'phoneEnable', true);
ProjectConfig::updateSubVar('userReg', 'phoneConfirm', true);
db()->query("TRUNCATE TABLE users");
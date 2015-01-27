<?php

Dir::clear('C:/1/captures');
SiteConfig::replaceConstant('more', 'TESTING', true);
SiteConfig::updateSubVar('userReg', 'emailEnable', false);
SiteConfig::updateSubVar('userReg', 'enable', true);
SiteConfig::updateSubVar('userReg', 'phoneEnable', true);
SiteConfig::updateSubVar('userReg', 'phoneConfirm', true);
db()->query("TRUNCATE TABLE users");
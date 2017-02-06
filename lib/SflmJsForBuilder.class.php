<?php

class SflmJsForBuilder extends SflmJs {

  function cacheFile($package) {
    Dir::make(Sflm::$webPath.'/'.$this->type);
    return Sflm::$webPath.'/'.$this->filePath($package);
  }

  function filePath($package) {
    return $this->type.'/'.str_replace('/', '-', $package).'.'.$this->type;
  }

}
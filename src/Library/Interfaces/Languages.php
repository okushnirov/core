<?php

namespace okushnirov\core\Library\Interfaces;

interface Languages
{
  public function bcp47():string;
  
  public function flag():string;
  
  public function iso():string;
  
  public function leid():int;
  
  public function short():string;
  
  public function title():string;
}
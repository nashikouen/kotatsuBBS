<?php
namespace Modules;

abstract class Module {
    abstract public function getName(): string;
    abstract public function getDescription(): string;
    abstract public function getVersion(): string;
    abstract public function init();
    abstract public function showPage(): string;
}
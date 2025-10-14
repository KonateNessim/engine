<?php
namespace App\Entity;

class MethodRequirement {
  private ?int $id = null;
  private Method $method;
  private string $label;
  private Argument $code;
  private bool $isRequired = true;
  private $defaultValue = null;
  private ?array $validationRules = null;

  public function getId(): ?int { return $this->id; }
  public function getMethod(): Method { return $this->method; }
  public function setMethod(Method $m): void { $this->method = $m; }
  public function getLabel(): string { return $this->label; }
  public function setLabel(string $l): void { $this->label = $l; }
  public function getCode(): Argument { return $this->code; }
  public function setCode(Argument $a): void { $this->code = $a; }
  public function isRequired(): bool { return $this->isRequired; }
  public function setIsRequired(bool $r): void { $this->isRequired = $r; }
  public function getDefaultValue() { return $this->defaultValue; }
  public function setDefaultValue($v): void { $this->defaultValue = $v; }
  public function getValidationRules(): ?array { return $this->validationRules; }
  public function setValidationRules(?array $v): void { $this->validationRules = $v; }
}

<?php
namespace App\DTO;
class AvenantDto
{
  public function __construct(public ?string $type=null, public ?\DateTimeInterface $date=null, public ?\DateTimeInterface $effet=null) {}
}

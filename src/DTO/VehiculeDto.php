<?php
namespace App\DTO;
class VehiculeDto
{
  public function __construct(
    public ?string $marque=null,
    public ?string $modele=null,
    public ?int $puissance=null,
    public ?string $energie=null,
    public ?string $usage=null
  ) {}
}

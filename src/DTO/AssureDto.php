<?php
namespace App\DTO;
class AssureDto
{
  public function __construct(
    public ?string $nom=null,
    public ?string $prenom=null,
    public ?\DateTimeInterface $dateNaissance=null,
    public ?int $age=null,
    public ?string $statut=null
  ) {}
}

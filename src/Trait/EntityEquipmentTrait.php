<?php

declare(strict_types=1);

namespace App\Trait;

use App\Entity\EquipmentStat;
use App\Enum\EquipmentSlot;
use App\Enum\StatType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\OneToMany;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\VirtualProperty;

trait EntityEquipmentTrait
{

}
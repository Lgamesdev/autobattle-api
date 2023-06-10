<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Equipment;
use App\Entity\EquipmentStat;
use App\Entity\UserCharacter;
use App\Enum\EquipmentSlot;
use App\Entity\Statistic;
use App\Enum\ItemQuality;
use App\Enum\StatType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class EquipmentFixtures extends Fixture /*implements DependentFixtureInterface*/
{
    private array $equipmentSets = [
        'Beginner' => array(),
        'Iron' => array(),
        'Barbarian' => array(),
        'Gladiator' => array(),
        'Infantry' => array(),
        'Knight' => array(),
    ];

    public function __construct()
    {

    }

    public function load(ObjectManager $manager): void
    {
        /////////////////////////////////// BEGINNER SET ////////////////////////////////////////////////////
        $weapon = new Equipment();
        $weapon->setEquipmentSlot(EquipmentSlot::WEAPON->value);
        $weapon->setName('Beginner Weapon');
        $weapon->setItemQuality(ItemQuality::NORMAL);
        $weapon->setRequiredLevel(2);
        $weapon->setSpriteId(1);
        $weapon->setCost(200);
        $weapon->setIconPath('Icons/Equipment/Weapon/Beginner');
        //$weapon->setIsDefaultItem(false);
        foreach (StatType::cases() as $statType) {
            $statValue = match ($statType) {
                StatType::HEALTH, StatType::ARMOR, StatType::AGILITY, StatType::INTELLIGENCE => null,
                StatType::STRENGTH => 8,
                StatType::LUCK => 2,
            };
            $weapon->stat($statType, $statValue);
        }
        $this->equipmentSets['Beginner'][] = $weapon;
//        $manager->persist($weapon);

        $chest = new Equipment();
        $chest->setEquipmentSlot(EquipmentSlot::CHEST->value);
        $chest->setName('Beginner Chest');
        $chest->setItemQuality(ItemQuality::NORMAL);
        $chest->setRequiredLevel(4);
        $chest->setSpriteId(1);
        $chest->setCost(450);
        $chest->setIconPath('Icons/Equipment/Chest/Beginner');
        foreach (StatType::cases() as $statType) {
            $statValue = match ($statType) {
                StatType::HEALTH => 25,
                StatType::ARMOR => 3,
                StatType::STRENGTH => 4,
                StatType::AGILITY, StatType::LUCK => 2,
                StatType::INTELLIGENCE => null,
            };
            $chest->stat($statType, $statValue);
        }
        $this->equipmentSets['Beginner'][] = $chest;
//        $manager->persist($chest);

        $pants = new Equipment();
        $pants->setEquipmentSlot(EquipmentSlot::PANTS->value);
        $pants->setName('Beginner Pants');
        $pants->setItemQuality(ItemQuality::NORMAL);
        $pants->setRequiredLevel(7);
        $pants->setSpriteId(1);
        $pants->setCost(650);
        $pants->setIconPath('Icons/Equipment/Pants/Beginner');
        foreach (StatType::cases() as $statType) {
            $statValue = match ($statType) {
                StatType::HEALTH => 30,
                StatType::ARMOR => 4,
                StatType::STRENGTH => 3,
                StatType::AGILITY, StatType::LUCK => 2,
                StatType::INTELLIGENCE => null
            };
            $pants->stat($statType, $statValue);
        }
        $this->equipmentSets['Beginner'][] = $pants;
//        $manager->persist($pants);


        /////////////////////////////////// IRON SET ////////////////////////////////////////////////////
        $weapon = new Equipment();
        $weapon->setEquipmentSlot(EquipmentSlot::WEAPON->value);
        $weapon->setName('Iron Weapon');
        $weapon->setItemQuality(ItemQuality::NORMAL);
        $weapon->setRequiredLevel(18);
        $weapon->setSpriteId(2);
        $weapon->setCost(1750);
        $weapon->setIconPath('Icons/Equipment/Weapon/Iron');
        foreach (StatType::cases() as $statType) {
            $statValue = match ($statType) {
                StatType::HEALTH, StatType::ARMOR, StatType::AGILITY, StatType::INTELLIGENCE => null,
                StatType::STRENGTH => 20,
                StatType::LUCK => 3,
            };
            $weapon->stat($statType, $statValue);
        }
        $this->equipmentSets['Iron'][] = $weapon;
//        $manager->persist($weapon);

        $helmet = new Equipment();
        $helmet->setEquipmentSlot(EquipmentSlot::HELMET->value);
        $helmet->setName('Iron Helmet');
        $helmet->setItemQuality(ItemQuality::NORMAL);
        $helmet->setRequiredLevel(15);
        $helmet->setSpriteId(2);
        $helmet->setCost(1000);
        $helmet->setIconPath('Icons/Equipment/Helmet/Iron');
        foreach (StatType::cases() as $statType) {
            $statValue = match ($statType) {
                StatType::HEALTH => 15,
                StatType::STRENGTH => 4,
                StatType::ARMOR => 3,
                StatType::AGILITY, StatType::LUCK => 2,
                StatType::INTELLIGENCE => null,
            };
            $helmet->stat($statType, $statValue);
        }
        $this->equipmentSets['Iron'][] = $helmet;
//        $manager->persist($helmet);

        $chest = new Equipment();
        $chest->setEquipmentSlot(EquipmentSlot::CHEST->value);
        $chest->setName('Iron Chest');
        $chest->setItemQuality(ItemQuality::NORMAL);
        $chest->setRequiredLevel(20);
        $chest->setSpriteId(2);
        $chest->setCost(2000);
        $chest->setIconPath('Icons/Equipment/Chest/Iron');
        foreach (StatType::cases() as $statType) {
            $statValue = match ($statType) {
                StatType::HEALTH => 60,
                StatType::ARMOR => 5,
                StatType::STRENGTH => 8,
                StatType::AGILITY, StatType::LUCK => 3,
                StatType::INTELLIGENCE => null,
            };
            $chest->stat($statType, $statValue);
        }
        $this->equipmentSets['Iron'][] = $chest;
//        $manager->persist($chest);

        $pants = new Equipment();
        $pants->setEquipmentSlot(EquipmentSlot::PANTS->value);
        $pants->setName('Iron Pants');
        $pants->setItemQuality(ItemQuality::NORMAL);
        $pants->setRequiredLevel(24);
        $pants->setSpriteId(2);
        $pants->setCost(2500);
        $pants->setIconPath('Icons/Equipment/Pants/Iron');
        foreach (StatType::cases() as $statType) {
            $statValue = match ($statType) {
                StatType::HEALTH => 75,
                StatType::ARMOR => 6,
                StatType::STRENGTH => 5,
                StatType::AGILITY, StatType::LUCK => 3,
                StatType::INTELLIGENCE => null
            };
            $pants->stat($statType, $statValue);
        }
        $this->equipmentSets['Iron'][] = $pants;
//        $manager->persist($pants);


        /////////////////////////////////// BARBARIAN SET ////////////////////////////////////////////////////
        $weapon = new Equipment();
        $weapon->setEquipmentSlot(EquipmentSlot::WEAPON->value);
        $weapon->setName('Barbarian Weapon');
        $weapon->setItemQuality(ItemQuality::NORMAL);
        $weapon->setRequiredLevel(35);
        $weapon->setSpriteId(3);
        $weapon->setCost(4000);
        $weapon->setIconPath('Icons/Equipment/Weapon/Barbarian');
        foreach (StatType::cases() as $statType) {
            $statValue = match ($statType) {
                StatType::HEALTH, StatType::ARMOR, StatType::AGILITY, StatType::INTELLIGENCE => null,
                StatType::STRENGTH => 40,
                StatType::LUCK => 4,
            };
            $weapon->stat($statType, $statValue);
        }
        $this->equipmentSets['Barbarian'][] = $weapon;
//        $manager->persist($weapon);

        $helmet = new Equipment();
        $helmet->setEquipmentSlot(EquipmentSlot::HELMET->value);
        $helmet->setName('Barbarian Helmet');
        $helmet->setItemQuality(ItemQuality::NORMAL);
        $helmet->setRequiredLevel(38);
        $helmet->setSpriteId(3);
        $helmet->setCost(4500);
        $helmet->setIconPath('Icons/Equipment/Helmet/Barbarian');
        foreach (StatType::cases() as $statType) {
            $statValue = match ($statType) {
                StatType::HEALTH => 30,
                StatType::STRENGTH => 8,
                StatType::ARMOR => 5,
                StatType::AGILITY, StatType::LUCK => 3,
                StatType::INTELLIGENCE => null,
            };
            $helmet->stat($statType, $statValue);
        }
        $this->equipmentSets['Barbarian'][] = $helmet;
//        $manager->persist($helmet);

        $chest = new Equipment();
        $chest->setEquipmentSlot(EquipmentSlot::CHEST->value);
        $chest->setName('Barbarian Chest');
        $chest->setItemQuality(ItemQuality::NORMAL);
        $chest->setRequiredLevel(42);
        $chest->setSpriteId(3);
        $chest->setCost(5000);
        $chest->setIconPath('Icons/Equipment/Chest/Barbarian');
        foreach (StatType::cases() as $statType) {
            $statValue = match ($statType) {
                StatType::HEALTH => 150,
                StatType::ARMOR => 10,
                StatType::STRENGTH => 15,
                StatType::AGILITY, StatType::LUCK => 4,
                StatType::INTELLIGENCE => null,
            };
            $chest->stat($statType, $statValue);
        }
        $this->equipmentSets['Barbarian'][] = $chest;
//        $manager->persist($chest);

        $pants = new Equipment();
        $pants->setEquipmentSlot(EquipmentSlot::PANTS->value);
        $pants->setName('Barbarian Pants');
        $pants->setItemQuality(ItemQuality::NORMAL);
        $pants->setRequiredLevel(45);
        $pants->setSpriteId(3);
        $pants->setCost(6000);
        $pants->setIconPath('Icons/Equipment/Pants/Iron');
        foreach (StatType::cases() as $statType) {
            $statValue = match ($statType) {
                StatType::HEALTH => 180,
                StatType::ARMOR => 15,
                StatType::STRENGTH => 8,
                StatType::AGILITY, StatType::LUCK => 4,
                StatType::INTELLIGENCE => null
            };
            $pants->stat($statType, $statValue);
        }
        $this->equipmentSets['Barbarian'][] = $pants;


        /////////////////////////////////// GLADIATOR SET ////////////////////////////////////////////////////
        $weapon = new Equipment();
        $weapon->setEquipmentSlot(EquipmentSlot::WEAPON->value);
        $weapon->setName('Gladiator Weapon');
        $weapon->setItemQuality(ItemQuality::NORMAL);
        $weapon->setRequiredLevel(50);
        $weapon->setSpriteId(4);
        $weapon->setCost(7500);
        $weapon->setIconPath('Icons/Equipment/Weapon/Gladiator');
        //$weapon->setIsDefaultItem(false);
        foreach (StatType::cases() as $statType) {
            $statValue = match ($statType) {
                StatType::HEALTH, StatType::ARMOR, StatType::AGILITY, StatType::INTELLIGENCE => null,
                StatType::STRENGTH => 75,
                StatType::LUCK => 5,
            };
            $weapon->stat($statType, $statValue);
        }
        $this->equipmentSets['Gladiator'][] = $weapon;

        $helmet = new Equipment();
        $helmet->setEquipmentSlot(EquipmentSlot::HELMET->value);
        $helmet->setName('Gladiator Helmet');
        $helmet->setItemQuality(ItemQuality::NORMAL);
        $helmet->setRequiredLevel(53);
        $helmet->setSpriteId(4);
        $helmet->setCost(8250);
        $helmet->setIconPath('Icons/Equipment/Helmet/Gladiator');
        foreach (StatType::cases() as $statType) {
            $statValue = match ($statType) {
                StatType::HEALTH => 50,
                StatType::STRENGTH => 12,
                StatType::ARMOR => 8,
                StatType::AGILITY, StatType::LUCK => 4,
                StatType::INTELLIGENCE => null,
            };
            $helmet->stat($statType, $statValue);
        }
        $this->equipmentSets['Gladiator'][] = $helmet;

        $chest = new Equipment();
        $chest->setEquipmentSlot(EquipmentSlot::CHEST->value);
        $chest->setName('Gladiator Chest');
        $chest->setItemQuality(ItemQuality::NORMAL);
        $chest->setRequiredLevel(56);
        $chest->setSpriteId(4);
        $chest->setCost(9000);
        $chest->setIconPath('Icons/Equipment/Chest/Gladiator');
        foreach (StatType::cases() as $statType) {
            $statValue = match ($statType) {
                StatType::HEALTH => 250,
                StatType::ARMOR => 12,
                StatType::STRENGTH => 25,
                StatType::AGILITY, StatType::LUCK => 5,
                StatType::INTELLIGENCE => null,
            };
            $chest->stat($statType, $statValue);
        }
        $this->equipmentSets['Gladiator'][] = $chest;

        $pants = new Equipment();
        $pants->setEquipmentSlot(EquipmentSlot::PANTS->value);
        $pants->setName('Gladiator Pants');
        $pants->setItemQuality(ItemQuality::NORMAL);
        $pants->setRequiredLevel(60);
        $pants->setSpriteId(4);
        $pants->setCost(12000);
        $pants->setIconPath('Icons/Equipment/Pants/Gladiator');
        foreach (StatType::cases() as $statType) {
            $statValue = match ($statType) {
                StatType::HEALTH => 300,
                StatType::ARMOR => 15,
                StatType::STRENGTH => 15,
                StatType::AGILITY, StatType::LUCK => 5,
                StatType::INTELLIGENCE => null
            };
            $pants->stat($statType, $statValue);
        }
        $this->equipmentSets['Gladiator'][] = $pants;


        /////////////////////////////////// INFANTRY SET ////////////////////////////////////////////////////
        $weapon = new Equipment();
        $weapon->setEquipmentSlot(EquipmentSlot::WEAPON->value);
        $weapon->setName('Infantry Weapon');
        $weapon->setItemQuality(ItemQuality::NORMAL);
        $weapon->setRequiredLevel(60);
        $weapon->setSpriteId(5);
        $weapon->setCost(15000);
        $weapon->setIconPath('Icons/Equipment/Weapon/Infantry');
        //$weapon->setIsDefaultItem(false);
        foreach (StatType::cases() as $statType) {
            $statValue = match ($statType) {
                StatType::HEALTH, StatType::ARMOR, StatType::AGILITY, StatType::INTELLIGENCE => null,
                StatType::STRENGTH => 120,
                StatType::LUCK => 6,
            };
            $weapon->stat($statType, $statValue);
        }
        $this->equipmentSets['Infantry'][] = $weapon;

        $helmet = new Equipment();
        $helmet->setEquipmentSlot(EquipmentSlot::HELMET->value);
        $helmet->setName('Infantry Helmet');
        $helmet->setItemQuality(ItemQuality::NORMAL);
        $helmet->setRequiredLevel(65);
        $helmet->setSpriteId(5);
        $helmet->setCost(20000);
        $helmet->setIconPath('Icons/Equipment/Helmet/Infantry');
        foreach (StatType::cases() as $statType) {
            $statValue = match ($statType) {
                StatType::HEALTH => 80,
                StatType::ARMOR => 15,
                StatType::STRENGTH => 20,
                StatType::AGILITY, StatType::LUCK => 5,
                StatType::INTELLIGENCE => null,
            };
            $helmet->stat($statType, $statValue);
        }
        $this->equipmentSets['Infantry'][] = $helmet;

        $chest = new Equipment();
        $chest->setEquipmentSlot(EquipmentSlot::CHEST->value);
        $chest->setName('Infantry Chest');
        $chest->setItemQuality(ItemQuality::NORMAL);
        $chest->setRequiredLevel(70);
        $chest->setSpriteId(5);
        $chest->setCost(25000);
        $chest->setIconPath('Icons/Equipment/Chest/Infantry');
        foreach (StatType::cases() as $statType) {
            $statValue = match ($statType) {
                StatType::HEALTH => 400,
                StatType::ARMOR => 25,
                StatType::STRENGTH => 40,
                StatType::AGILITY, StatType::LUCK => 6,
                StatType::INTELLIGENCE => null,
            };
            $chest->stat($statType, $statValue);
        }
        $this->equipmentSets['Infantry'][] = $chest;

        $pants = new Equipment();
        $pants->setEquipmentSlot(EquipmentSlot::PANTS->value);
        $pants->setName('Infantry Pants');
        $pants->setItemQuality(ItemQuality::NORMAL);
        $pants->setRequiredLevel(75);
        $pants->setSpriteId(5);
        $pants->setCost(30000);
        $pants->setIconPath('Icons/Equipment/Pants/Infantry');
        foreach (StatType::cases() as $statType) {
            $statValue = match ($statType) {
                StatType::HEALTH => 500,
                StatType::ARMOR => 30,
                StatType::STRENGTH => 30,
                StatType::AGILITY, StatType::LUCK => 6,
                StatType::INTELLIGENCE => null
            };
            $pants->stat($statType, $statValue);
        }
        $this->equipmentSets['Infantry'][] = $pants;


        /////////////////////////////////// KNIGHT SET ////////////////////////////////////////////////////
        $weapon = new Equipment();
        $weapon->setEquipmentSlot(EquipmentSlot::WEAPON->value);
        $weapon->setName('Knight Weapon');
        $weapon->setItemQuality(ItemQuality::NORMAL);
        $weapon->setRequiredLevel(85);
        $weapon->setSpriteId(6);
        $weapon->setCost(40000);
        $weapon->setIconPath('Icons/Equipment/Weapon/Knight');
        foreach (StatType::cases() as $statType) {
            $statValue = match ($statType) {
                StatType::HEALTH, StatType::ARMOR, StatType::AGILITY, StatType::INTELLIGENCE => null,
                StatType::STRENGTH => 250,
                StatType::LUCK => 7,
            };
            $weapon->stat($statType, $statValue);
        }
        $this->equipmentSets['Knight'][] = $weapon;

        $helmet = new Equipment();
        $helmet->setEquipmentSlot(EquipmentSlot::HELMET->value);
        $helmet->setName('Knight Helmet');
        $helmet->setItemQuality(ItemQuality::NORMAL);
        $helmet->setRequiredLevel(90);
        $helmet->setSpriteId(6);
        $helmet->setCost(50000);
        $helmet->setIconPath('Icons/Equipment/Helmet/Knight');
        foreach (StatType::cases() as $statType) {
            $statValue = match ($statType) {
                StatType::HEALTH => 200,
                StatType::ARMOR => 30,
                StatType::STRENGTH => 50,
                StatType::AGILITY, StatType::LUCK => 7,
                StatType::INTELLIGENCE => null,
            };
            $helmet->stat($statType, $statValue);
        }
        $this->equipmentSets['Knight'][] = $helmet;

        $chest = new Equipment();
        $chest->setEquipmentSlot(EquipmentSlot::CHEST->value);
        $chest->setName('Knight Chest');
        $chest->setItemQuality(ItemQuality::NORMAL);
        $chest->setRequiredLevel(95);
        $chest->setSpriteId(6);
        $chest->setCost(60000);
        $chest->setIconPath('Icons/Equipment/Chest/Knight');
        foreach (StatType::cases() as $statType) {
            $statValue = match ($statType) {
                StatType::HEALTH => 1000,
                StatType::ARMOR => 50,
                StatType::STRENGTH => 100,
                StatType::AGILITY, StatType::LUCK => 7,
                StatType::INTELLIGENCE => null,
            };
            $chest->stat($statType, $statValue);
        }
        $this->equipmentSets['Knight'][] = $chest;

        $pants = new Equipment();
        $pants->setEquipmentSlot(EquipmentSlot::PANTS->value);
        $pants->setName('Knight Pants');
        $pants->setItemQuality(ItemQuality::NORMAL);
        $pants->setRequiredLevel(100);
        $pants->setSpriteId(6);
        $pants->setCost(75000);
        $pants->setIconPath('Icons/Equipment/Pants/Knight');
        foreach (StatType::cases() as $statType) {
            $statValue = match ($statType) {
                StatType::HEALTH => 1500,
                StatType::ARMOR => 60,
                StatType::STRENGTH => 80,
                StatType::AGILITY, StatType::LUCK => 7,
                StatType::INTELLIGENCE => null
            };
            $pants->stat($statType, $statValue);
        }
        $this->equipmentSets['Knight'][] = $pants;


        ////////////////////////////////// ITEM QUALITY GENERATION ///////////////////////////////////////////
        foreach ($this->equipmentSets as &$equipmentSet)
        {
            /** @var Equipment $equipment */
            foreach ($equipmentSet as $equipment) {
                $rareEquipment = new Equipment();
                $rareEquipment->setName($equipment->getName());
                $rareEquipment->setEquipmentSlot($equipment->getEquipmentSlotValue());
                $rareEquipment->setIconPath($equipment->getIconPath());
                $rareEquipment->setSpriteId($equipment->getSpriteId());
                $rareEquipment->setItemQuality(ItemQuality::RARE);
                $rareEquipment->setRequiredLevel(min(($equipment->getRequiredLevel() + 5), UserCharacter::MAX_LEVEL));
                $rareEquipment->setCost((int)($equipment->getCost() * 1.25));

                /** @var EquipmentStat $stat */
                foreach ($equipment->getStats() as $stat) {
                    $rareEquipment->stat($stat->getStat(), (int)($stat->getValue() * 1.25));
                }
                $equipmentSet[] = $rareEquipment;

                $epicEquipment = new Equipment();
                $epicEquipment->setName($equipment->getName());
                $epicEquipment->setEquipmentSlot($equipment->getEquipmentSlotValue());
                $epicEquipment->setIconPath($equipment->getIconPath());
                $epicEquipment->setSpriteId($equipment->getSpriteId());
                $epicEquipment->setItemQuality(ItemQuality::EPIC);
                $epicEquipment->setRequiredLevel(min(($equipment->getRequiredLevel() + 8), UserCharacter::MAX_LEVEL));
                $epicEquipment->setCost((int)($equipment->getCost() * 1.5));

                /** @var EquipmentStat $stat */
                foreach ($equipment->getStats() as $stat) {
                    $epicEquipment->stat($stat->getStat(), (int)($stat->getValue() * 1.5));
                }
                $equipmentSet[] = $epicEquipment;

                $manager->persist($equipment);
                $manager->persist($rareEquipment);
                $manager->persist($epicEquipment);
            }
        }

        $manager->flush();
    }

	/*function getDependencies(): array
    {
        return [
        ];
	}*/
}

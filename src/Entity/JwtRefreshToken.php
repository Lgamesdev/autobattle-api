<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use \Gesdinet\JWTRefreshTokenBundle\Entity\RefreshToken;

#[Entity]
#[Table(name: 'jwt_refresh_token')]
class JwtRefreshToken extends RefreshToken
{

}
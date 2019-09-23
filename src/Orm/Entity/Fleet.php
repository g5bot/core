<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Stu\Orm\Repository\ShipRepositoryInterface;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\FleetRepository")
 * @Table(
 *     name="stu_fleets",
 *     indexes={
 *         @Index(name="user_idx", columns={"user_id"})
 *     }
 * )
 **/
class Fleet implements FleetInterface
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    private $id;

    /** @Column(type="string", length=200) */
    private $name = '';

    /** @Column(type="integer") */
    private $user_id = 0;

    /** @Column(type="integer") */
    private $ships_id = 0;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @OneToMany(targetEntity="Ship", mappedBy="fleet")
     */
    private $shiplist;

    /**
     * @OneToOne(targetEntity="Ship")
     * @JoinColumn(name="ships_id", referencedColumnName="id")
     */
    private $fleetLeader;

    public function __construct()
    {
        $this->shiplist = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): FleetInterface
    {
        $this->name = $name;
        return $this;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getShips(): iterable
    {
        return $this->shiplist;
    }

    public function getShipCount(): int
    {
        return $this->getShips()->count();
    }

    public function ownedByCurrentUser(): bool
    {
        return currentUser()->getId() === $this->getUserId();
    }

    public function getLeadShip(): ShipInterface
    {
        return $this->fleetLeader;
    }

    public function setLeadShip(ShipInterface $ship): FleetInterface
    {
        $this->fleetLeader = $ship;
        return $this;
    }

    public function getAvailableShips(): iterable
    {
        // @todo refactor
        global $container;

        return array_filter(
            $container->get(ShipRepositoryInterface::class)->getByUser($this->getUserId()),
            function (ShipInterface $ship): bool {
                if ($ship->isBase() || $ship->getFleet() !== null) {
                    return false;
                }
                $leader = $this->getLeadShip();

                if ($leader->getSystemsId() !== $ship->getSystemsId()) {
                    return false;
                }
                if ($leader->getSystemsId() > 0) {
                    return $ship->getSx() === $leader->getSX() && $ship->getSy() === $leader->getSY();
                }
                return $ship->getCx() === $leader->getCX() && $ship->getCy() === $leader->getCY();
            }
        );
    }

    public function deactivateSystem(int $system): void
    {
        foreach ($this->getShips() as $ship) {
            $ship->deactivateSystem($system);
            $ship->save();
        }
    }

    public function activateSystem(int $system): void
    {
        foreach ($this->getShips() as $ship) {
            $ship->activateSystem($system);
            $ship->save();
        }
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): FleetInterface
    {
        $this->user = $user;
        return $this;
    }

    public function getPointSum(): int
    {
        return (int)DB()->query(
            'SELECT SUM(c.points) FROM stu_ships as a LEFT JOIN stu_rumps as b ON (b.id=a.rumps_id) LEFT JOIN stu_rumps_categories as c ON (c.id=b.category_id) WHERE a.fleets_id=' . $this->getId(),
            1
        );
    }
}

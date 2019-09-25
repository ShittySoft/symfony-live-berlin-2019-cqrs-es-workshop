<?php

declare(strict_types=1);

namespace Building\Domain\Aggregate;

use Building\Domain\DomainEvent\CheckInAnomalyDetected;
use Building\Domain\DomainEvent\NewBuildingWasRegistered;
use Building\Domain\DomainEvent\UserCheckedIn;
use Building\Domain\DomainEvent\UserCheckedOut;
use Prooph\EventSourcing\AggregateRoot;
use Rhumsaa\Uuid\Uuid;
use function array_key_exists;

final class Building extends AggregateRoot
{
    /**
     * @var Uuid
     */
    private $uuid;

    /**
     * @var string
     */
    private $name;

    /** @var array<string, null> */
    private $checkedInUsers = [];

    public static function new(string $name) : self
    {
        $self = new self();

        $self->recordThat(NewBuildingWasRegistered::occur(
            (string) Uuid::uuid4(),
            [
                'name' => $name
            ]
        ));

        return $self;
    }

    public function checkInUser(string $username)
    {
        $anomalyDetected = array_key_exists($username, $this->checkedInUsers);

        $this->recordThat(UserCheckedIn::toBuilding($this->uuid, $username));

        if ($anomalyDetected) {
            $this->recordThat(CheckInAnomalyDetected::inBuildingForUser($this->uuid, $username));
        }
    }

    public function checkOutUser(string $username)
    {
        $anomalyDetected = ! array_key_exists($username, $this->checkedInUsers);

        $this->recordThat(UserCheckedOut::ofBuilding($this->uuid, $username));

        if ($anomalyDetected) {
            $this->recordThat(CheckInAnomalyDetected::inBuildingForUser($this->uuid, $username));
        }
    }

    public function whenNewBuildingWasRegistered(NewBuildingWasRegistered $event)
    {
        $this->uuid = Uuid::fromString($event->aggregateId());
        $this->name = $event->name();
    }

    protected function whenUserCheckedIn(UserCheckedIn $event)
    {
        $this->checkedInUsers[$event->username()] = null;
    }

    protected function whenUserCheckedOut(UserCheckedOut $event)
    {
        unset($this->checkedInUsers[$event->username()]);
    }

    protected function whenCheckInAnomalyDetected(CheckInAnomalyDetected $event)
    {
        // Empty on purpose - no state mutation for this event
    }

    /**
     * {@inheritDoc}
     */
    protected function aggregateId() : string
    {
        return (string) $this->uuid;
    }
}

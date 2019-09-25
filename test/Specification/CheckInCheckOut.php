<?php

declare(strict_types=1);

namespace Specification;

use Assert\Assertion;
use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use Building\Domain\Aggregate\Building;
use Building\Domain\DomainEvent\NewBuildingWasRegistered;
use Building\Domain\DomainEvent\UserCheckedIn;
use Prooph\EventSourcing\AggregateChanged;
use Prooph\EventSourcing\EventStoreIntegration\AggregateTranslator;
use Prooph\EventStore\Aggregate\AggregateType;
use Rhumsaa\Uuid\Uuid;

final class CheckInCheckOut implements Context
{
    /** @var Uuid|null */
    private $id;

    /** @var AggregateChanged[] */
    private $history = [];

    /** @var AggregateChanged[]|null */
    private $recordedEvents;

    /** @var Building|null */
    private $building;

    /** @Given /^a building$/ */
    public function aBuilding()
    {
        $this->id        = Uuid::uuid4();
        $this->history[] = NewBuildingWasRegistered::occur(
            $this->id->toString(),
            ['name' => 'a building']
        );
    }

    /** @When /^"([^"]*)" checks into the building$/ */
    public function checksIntoTheBuilding(string $username)
    {
        $this->building()
            ->checkInUser($username);
    }

    /** @Then /^"([^"]*)" should have been checked into the building$/ */
    public function shouldHaveBeenCheckedIntoTheBuilding(string $username)
    {
        /** @var UserCheckedIn $event */
        $event = $this->popNextRecordedEvent();

        Assertion::isInstanceOf($event, UserCheckedIn::class);
        Assertion::same($event->username(), $username);
    }

    private function building() : Building
    {
        return $this->building ??
            $this->building = (new AggregateTranslator())
            ->reconstituteAggregateFromHistory(
                AggregateType::fromAggregateRootClass(Building::class),
                new \ArrayIterator($this->history)
            );
    }

    private function popNextRecordedEvent() : AggregateChanged
    {
        if (null !== $this->recordedEvents) {
            return array_shift($this->recordedEvents);
        }

        $this->recordedEvents = (new AggregateTranslator())
            ->extractPendingStreamEvents($this->building());

        return array_shift($this->recordedEvents);
    }
}

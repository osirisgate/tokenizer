<?php

/*
 * This file is part of the Osirisgate package.
 *
 * (c) Ulrich Geraud AHOGLA <developer@osirisgate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Osirisgate\Component\Tokenizer\Shared\Entity;

/**
 * An abstract base class for entities within the Tokenizer component.
 *
 * This class provides common properties and methods for entity management,
 * such as a unique identifier, entity validity tracking, and error context storage.
 * Concrete entity classes should extend this base class to inherit these functionalities.
 *
 * @author Ulrich Geraud AHOGLA <developer@osirisgate.com>
 */
abstract class BaseEntity
{
    /**
     * @var string|null The unique identifier of the entity. May be null if the entity has not yet been persisted.
     */
    protected ?string $id;

    /**
     * @var bool a flag indicating whether the entity is considered valid based on its internal state or validation rules
     */
    private bool $isValidEntity;

    /**
     * @var array<string, mixed> an associative array to store context-specific error information related to the entity's invalid state
     */
    private array $errorContext;

    /**
     * Constructor for the `BaseEntity` class.
     *
     * Initializes the entity with an optional ID, sets the initial validity to false,
     * and initializes an empty error context array.
     *
     * @param string|null $id The initial unique identifier for the entity. Defaults to null.
     */
    protected function __construct(?string $id = null)
    {
        $this->id = $id;
        $this->isValidEntity = false;
        $this->errorContext = [];
    }

    /**
     * Returns the unique identifier of the entity.
     *
     * @return string|null the entity's ID, or null if it hasn't been assigned yet
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Checks if the entity is currently considered valid.
     *
     * @return bool true if the entity is valid, false otherwise
     */
    public function isValidEntity(): bool
    {
        return $this->isValidEntity;
    }

    /**
     * Returns the error context associated with the entity.
     *
     * This array can contain key-value pairs providing details about why the entity might be invalid.
     *
     * @return array<string, mixed> the associative array containing error context information
     */
    public function getErrorContext(): array
    {
        return $this->errorContext;
    }

    /**
     * Adds a key-value pair to the error context of the entity.
     *
     * This method allows storing specific details about validation failures or invalid states.
     *
     * @param string $key   the key for the error context information
     * @param mixed  $value the value associated with the error context key
     */
    public function addErrorContext(string $key, mixed $value): void
    {
        $this->errorContext[$key] = $value;
    }

    /**
     * Marks the entity as valid.
     *
     * This method should be called when the entity's state is determined to be valid
     * according to its defined rules.
     */
    protected function markAsValid(): void
    {
        $this->isValidEntity = true;
    }

    /**
     * Marks the entity as invalid.
     *
     * This method should be called when the entity's state violates its defined rules.
     * You might also want to populate the error context using {@see addErrorContext()}
     * to provide more information about the invalid state.
     */
    protected function markAsInvalid(): void
    {
        $this->isValidEntity = false;
    }
}

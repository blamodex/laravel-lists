<?php

declare(strict_types=1);

namespace Blamodex\Lists\Tests\Unit;

use Blamodex\Lists\Exceptions\InvalidListableException;
use Blamodex\Lists\Exceptions\InvalidListAttributeException;
use Blamodex\Lists\Exceptions\ListException;
use Blamodex\Lists\Exceptions\ListOwnershipException;
use Blamodex\Lists\Tests\TestCase;
use Exception;

class ExceptionsTest extends TestCase
{
    // ==========================================
    // ListException tests
    // ==========================================

    public function testListExceptionExtendsException(): void
    {
        $exception = new ListException('Test message');

        $this->assertInstanceOf(Exception::class, $exception);
    }

    public function testListExceptionCanBeThrown(): void
    {
        $this->expectException(ListException::class);
        $this->expectExceptionMessage('Test message');

        throw new ListException('Test message');
    }

    // ==========================================
    // ListOwnershipException tests
    // ==========================================

    public function testListOwnershipExceptionExtendsListException(): void
    {
        $exception = ListOwnershipException::notOwner();

        $this->assertInstanceOf(ListException::class, $exception);
    }

    public function testListOwnershipExceptionNotOwnerFactoryMethod(): void
    {
        $exception = ListOwnershipException::notOwner();

        $this->assertInstanceOf(ListOwnershipException::class, $exception);
        $this->assertEquals('The provided list is not owned by this model.', $exception->getMessage());
    }

    public function testListOwnershipExceptionCanBeCaughtAsListException(): void
    {
        $caught = false;

        try {
            throw ListOwnershipException::notOwner();
        } catch (ListException $e) {
            $caught = true;
            $this->assertInstanceOf(ListOwnershipException::class, $e);
        }

        $this->assertTrue($caught, 'Exception was not caught');
    }

    // ==========================================
    // InvalidListableException tests
    // ==========================================

    public function testInvalidListableExceptionExtendsListException(): void
    {
        $exception = InvalidListableException::unsavedModel();

        $this->assertInstanceOf(ListException::class, $exception);
    }

    public function testInvalidListableExceptionUnsavedModelFactoryMethod(): void
    {
        $exception = InvalidListableException::unsavedModel();

        $this->assertInstanceOf(InvalidListableException::class, $exception);
        $this->assertEquals('The listable model must be saved before adding to a list.', $exception->getMessage());
    }

    public function testInvalidListableExceptionCanBeCaughtAsListException(): void
    {
        $caught = false;

        try {
            throw InvalidListableException::unsavedModel();
        } catch (ListException $e) {
            $caught = true;
            $this->assertInstanceOf(InvalidListableException::class, $e);
        }

        $this->assertTrue($caught, 'Exception was not caught');
    }

    // ==========================================
    // InvalidListAttributeException tests
    // ==========================================

    public function testInvalidListAttributeExceptionExtendsListException(): void
    {
        $exception = InvalidListAttributeException::missingAttribute('name');

        $this->assertInstanceOf(ListException::class, $exception);
    }

    public function testInvalidListAttributeExceptionMissingAttributeFactoryMethod(): void
    {
        $exception = InvalidListAttributeException::missingAttribute('name');

        $this->assertInstanceOf(InvalidListAttributeException::class, $exception);
        $this->assertEquals('The "name" attribute is required to create a list.', $exception->getMessage());
    }

    public function testInvalidListAttributeExceptionMissingAttributeWithDifferentAttribute(): void
    {
        $exception = InvalidListAttributeException::missingAttribute('slug');

        $this->assertEquals('The "slug" attribute is required to create a list.', $exception->getMessage());
    }

    public function testInvalidListAttributeExceptionEmptyAttributeFactoryMethod(): void
    {
        $exception = InvalidListAttributeException::emptyAttribute('name');

        $this->assertInstanceOf(InvalidListAttributeException::class, $exception);
        $this->assertEquals('The "name" attribute cannot be empty.', $exception->getMessage());
    }

    public function testInvalidListAttributeExceptionEmptyAttributeWithDifferentAttribute(): void
    {
        $exception = InvalidListAttributeException::emptyAttribute('description');

        $this->assertEquals('The "description" attribute cannot be empty.', $exception->getMessage());
    }

    public function testInvalidListAttributeExceptionCanBeCaughtAsListException(): void
    {
        $caught = false;

        try {
            throw InvalidListAttributeException::missingAttribute('name');
        } catch (ListException $e) {
            $caught = true;
            $this->assertInstanceOf(InvalidListAttributeException::class, $e);
        }

        $this->assertTrue($caught, 'Exception was not caught');
    }

    // ==========================================
    // Exception hierarchy tests
    // ==========================================

    public function testAllExceptionsCanBeCaughtByBaseListException(): void
    {
        $exceptions = [
            ListOwnershipException::notOwner(),
            InvalidListableException::unsavedModel(),
            InvalidListAttributeException::missingAttribute('name'),
        ];

        foreach ($exceptions as $exception) {
            $this->assertInstanceOf(ListException::class, $exception);
        }
    }

    public function testExceptionHierarchyAllowsSpecificCatching(): void
    {
        $caught = false;

        try {
            throw ListOwnershipException::notOwner();
        } catch (ListOwnershipException $e) {
            $caught = true;
            $this->assertInstanceOf(ListOwnershipException::class, $e);
        }

        $this->assertTrue($caught, 'ListOwnershipException should be caught by specific catch block');
    }
}

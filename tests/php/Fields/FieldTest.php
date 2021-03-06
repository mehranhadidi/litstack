<?php

namespace Tests\Fields;

use Ignite\Crud\BaseForm;
use Ignite\Crud\Field;
use Ignite\Crud\FieldDependency;
use Ignite\Exceptions\Traceable\MissingAttributeException;
use Illuminate\Support\Facades\Auth;
use Lit\Models\User;
use Tests\BackendTestCase;
use Tests\Traits\InteractsWithFields;

class FieldTest extends BackendTestCase
{
    use InteractsWithFields;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function test_getTitle_method()
    {
        $field = $this->getField(DummyField::class);

        $this->setUnaccessibleProperty($field, 'attributes', ['id' => 'my_field']);
        $this->assertEquals('My Field', $field->getTitle());

        $this->setUnaccessibleProperty($field, 'attributes', ['id' => 'other']);
        $this->assertEquals('Other', $field->getTitle());
    }

    /** @test */
    public function it_merges_trait_attributes()
    {
        $field = $this->getField(DummyField::class);

        $this->assertContains('trait_required', $field->required);
    }

    /** @test */
    public function it_sets_default_attributes()
    {
        $field = $this->getField(DummyField::class);

        $attributes = $field->getAttributes();

        $this->assertArrayHasKey('dummyTraitAttribute', $attributes);
        $this->assertEquals('some_value', $attributes['dummyTraitAttribute']);
    }

    /** @test */
    public function it_sets_default_attribute_from_method()
    {
        $field = $this->getField(DummyField::class);

        $attributes = $field->getAttributes();

        $this->assertArrayHasKey('default', $attributes);
        $this->assertEquals('value', $attributes['default']);
    }

    /** @test */
    public function test_checkComplete_method()
    {
        $field = $this->getField(DummyField::class);
        $field->required = ['title'];

        $this->expectException(MissingAttributeException::class);
        $field->checkComplete();

        $field->setAttribute('title', 'something');
        $this->assertTrue($field->checkComplete());
    }

    /** @test */
    public function test_readonly_method()
    {
        $field = $this->getField(DummyField::class);

        $field->readonly();
        $this->assertTrue($field->getAttribute('readonly'));

        $field->readonly(false);
        $this->assertFalse($field->getAttribute('readonly'));

        // Assert method returns field instance.
        $this->assertEquals($field, $field->readonly());
    }

    /** @test */
    public function test_authorized_method()
    {
        $field = $this->getField(DummyField::class);

        $field->authorize(function () {
            return true;
        });
        $this->assertTrue($field->check());

        $field->authorize(function () {
            return false;
        });
        $this->assertFalse($field->check());
    }

    /** @test */
    public function test_authorized_passes_logged_in_lit_user_to_closure()
    {
        $field = $this->getField(DummyField::class);

        $User = factory(User::class)->create([
            'username' => 'dummy_lit_user',
        ]);

        Auth::guard('lit')->login($User);

        $field->authorize(function ($user) use ($User) {
            $this->assertInstanceOf(User::class, $user);
            $this->assertEquals($user, $User);

            return true;
        });
        $field->check();
    }

    /** @test */
    public function test_dependency_using_field_instance()
    {
        $form = new BaseForm('model');
        $dependent = $form->input('dependent');
        $field = $form->input('field');

        $this->assertSame($field, $field->when($dependent, 'dummy-value'));
        $this->assertCount(1, $field->getDependencies());
        $this->assertInstanceOf(FieldDependency::class, $dependency = $field->getDependencies()->first());
        $this->assertSame($dependent->id, $dependency->getAttributeName());
    }

    /** @test */
    public function test_dependency_using_field_id()
    {
        $form = new BaseForm('model');
        $dependent = $form->input('dependent');
        $field = $form->input('field');

        $this->assertSame($field, $field->when('dependent', 'dummy-value'));
        $this->assertCount(1, $field->getDependencies());
        $this->assertInstanceOf(FieldDependency::class, $dependency = $field->getDependencies()->first());
        $this->assertSame($dependent->id, $dependency->getAttributeName());
    }
}

trait DummyFieldTrait
{
    public $traitRequired = ['trait_required'];

    public function setDummyTraitAttributeDefault()
    {
        return 'some_value';
    }
}

class DummyField extends Field
{
    use DummyFieldTrait;

    public function mount()
    {
        $this->setAttribute('default', 'value');
    }
}

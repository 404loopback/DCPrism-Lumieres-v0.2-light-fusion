<?php

namespace Tests\Unit;

use Modules\Fresnel\app\Models\Parameter;
use Tests\TestCase;

class ParameterFormattingTest extends TestCase
{
    /**
     * Test des règles de formatage simples
     */
    public function test_simple_formatting_rules()
    {
        $parameter = new Parameter();
        
        // Test no_spacing
        $parameter->format_rules = 'no_spacing';
        $result = $parameter->applyFormatting('Hello World');
        $this->assertEquals('HelloWorld', $result);
        
        // Test upper_case
        $parameter->format_rules = 'upper_case';
        $result = $parameter->applyFormatting('hello world');
        $this->assertEquals('HELLO WORLD', $result);
        
        // Test lower_case
        $parameter->format_rules = 'lower_case';
        $result = $parameter->applyFormatting('HELLO WORLD');
        $this->assertEquals('hello world', $result);
        
        // Test trim
        $parameter->format_rules = 'trim';
        $result = $parameter->applyFormatting('  hello world  ');
        $this->assertEquals('hello world', $result);
    }
    
    /**
     * Test des règles de formatage complexes
     */
    public function test_complex_formatting_rules()
    {
        $parameter = new Parameter();
        
        // Test camel_case
        $parameter->format_rules = 'camel_case';
        $result = $parameter->applyFormatting('hello world test');
        $this->assertEquals('helloWorldTest', $result);
        
        // Test snake_case
        $parameter->format_rules = 'snake_case';
        $result = $parameter->applyFormatting('Hello World Test');
        $this->assertEquals('hello_world_test', $result);
        
        // Test kebab_case
        $parameter->format_rules = 'kebab_case';
        $result = $parameter->applyFormatting('Hello World Test');
        $this->assertEquals('hello-world-test', $result);
    }
    
    /**
     * Test des règles multiples
     */
    public function test_multiple_formatting_rules()
    {
        $parameter = new Parameter();
        
        // Test combinaison: trim + no_spacing + upper_case
        $parameter->format_rules = 'trim,no_spacing,upper_case';
        $result = $parameter->applyFormatting('  hello world  ');
        $this->assertEquals('HELLOWORLD', $result);
        
        // Test combinaison: trim + camel_case
        $parameter->format_rules = 'trim,camel_case';
        $result = $parameter->applyFormatting('  hello world test  ');
        $this->assertEquals('helloWorldTest', $result);
    }
    
    /**
     * Test de suppression des accents
     */
    public function test_remove_accents()
    {
        $parameter = new Parameter();
        $parameter->format_rules = 'no_accents';
        
        $result = $parameter->applyFormatting('Café français à Noël');
        $this->assertEquals('Cafe francais a Noel', $result);
    }
    
    /**
     * Test avec règle inexistante
     */
    public function test_unknown_rule()
    {
        $parameter = new Parameter();
        $parameter->format_rules = 'unknown_rule';
        
        $result = $parameter->applyFormatting('hello world');
        $this->assertEquals('hello world', $result); // Pas de modification
    }
    
    /**
     * Test des règles de crochets et parenthèses
     */
    public function test_brackets_and_parentheses_rules()
    {
        $parameter = new Parameter();
        
        // Test brackets (tout le texte)
        $parameter->format_rules = 'brackets';
        $result = $parameter->applyFormatting('hello world');
        $this->assertEquals('[hello world]', $result);
        
        // Test each_brackets (chaque mot)
        $parameter->format_rules = 'each_brackets';
        $result = $parameter->applyFormatting('hello world test');
        $this->assertEquals('[hello] [world] [test]', $result);
        
        // Test parentheses (tout le texte)
        $parameter->format_rules = 'parentheses';
        $result = $parameter->applyFormatting('hello world');
        $this->assertEquals('(hello world)', $result);
        
        // Test each_parentheses (chaque mot)
        $parameter->format_rules = 'each_parentheses';
        $result = $parameter->applyFormatting('hello world test');
        $this->assertEquals('(hello) (world) (test)', $result);
    }
    
    /**
     * Test des espaces multiples avec each_brackets/each_parentheses
     */
    public function test_multiple_spaces_with_each_rules()
    {
        $parameter = new Parameter();
        
        // Test avec espaces multiples - doivent être nettoyés
        $parameter->format_rules = 'each_brackets';
        $result = $parameter->applyFormatting('hello  world   test');
        $this->assertEquals('[hello] [world] [test]', $result);
        
        $parameter->format_rules = 'each_parentheses';
        $result = $parameter->applyFormatting('  hello  world  ');
        $this->assertEquals('(hello) (world)', $result);
    }

    /**
     * Test sans règles
     */
    public function test_no_formatting_rules()
    {
        $parameter = new Parameter();
        $parameter->format_rules = null;
        
        $result = $parameter->applyFormatting('hello world');
        $this->assertEquals('hello world', $result);
        
        $parameter->format_rules = '';
        $result = $parameter->applyFormatting('hello world');
        $this->assertEquals('hello world', $result);
    }
}

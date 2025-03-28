<?php

namespace NeuronAI\Tests;

use NeuronAI\Agent;
use NeuronAI\AgentInterface;
use NeuronAI\Chat\Messages\AssistantMessage;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\RAG\RAG;
use NeuronAI\Tools\Tool;
use NeuronAI\Chat\Messages\ToolCallMessage;
use NeuronAI\Chat\Messages\ToolCallResultMessage;
use NeuronAI\Tests\Doubles\InMemoryAIProvider;
use NeuronAI\Tools\ToolInterface;
use PHPUnit\Framework\TestCase;

class NeuronAITest extends TestCase
{
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @throws \Exception
     */
    public function setup(): void {}

    public function test_agent_instance()
    {
        $neuron = new Agent();
        $this->assertInstanceOf(AgentInterface::class, $neuron);

        $neuron = new RAG();
        $this->assertInstanceOf(Agent::class, $neuron);
    }

    public function test_message_instance()
    {
        $tools = [
            new Tool('example', 'example')
        ];

        $this->assertInstanceOf(Message::class, new UserMessage(''));
        $this->assertInstanceOf(Message::class, new AssistantMessage(''));
        $this->assertInstanceOf(Message::class, new ToolCallMessage('', $tools));
    }

    public function test_tool_instance()
    {
        $tool = new Tool('example', 'example');
        $this->assertInstanceOf(ToolInterface::class, $tool);
    }

    public function testToolMessageHandling()
    {
        $tool = new Tool(
            name: 'example_tool',
            description: 'This is a description',
        )->setCallable(function () {
            return 'foo';
        });

        $provider = new InMemoryAIProvider(
            responses: [
                new ToolCallMessage(
                    content: null,
                    tools: [$tool],
                ),
                new AssistantMessage('This is a reply from the assistant')
            ],
        );
        $agent = new Agent()->setProvider($provider);
        $response = $agent->chat(new Message('Testing'));

        // We expect 4 messages in history only:
        // 1. Original Chat Message
        // 2. The tool call message
        // 3. The ToolCallResultMessage once agent processes the tool call
        // 4. The AssistantMessage response
        $this->assertCount(4, $agent->resolveChatHistory()->getMessages());
    }
}

<?php

namespace NeuronAI\Tests\Doubles;

use Generator;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\HandleWithTools;

class InMemoryAIProvider implements AIProviderInterface
{
    use HandleWithTools;

    /**
     * @param Message[] $responses
     */
    public function __construct(
        private array $responses = [],
        private ?string $systemPrompt = null,
    ) {}

    public function addResponse(Message $response): static
    {
        $this->responses[] = $response;

        return $this;
    }

    public function systemPrompt(?string $prompt): AIProviderInterface
    {
        $this->systemPrompt = $prompt;

        return $this;
    }

    public function chat(array $messages): Message
    {
        return array_shift($this->responses);
    }

    public function stream(array|string $messages, callable $executeToolsCallback): Generator
    {
        yield array_shift($this->responses);
    }
}

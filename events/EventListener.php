<?php

namespace kadcore\tcphpmvc\events;

class EventListener
{
    public array $listeners = [];

    public function triggerEvent(string $eventType)
    {
        $callbacks = $this->listeners[$eventType] ?? [];
        foreach ($callbacks as $callback) {
            \call_user_func($callback);
        }
    }

    /**
     * ativa uma função para o evento desejado
     * 
     * @param string $eventType Use EventType::
     * @param mixed $calbackFunction 
     * @return void 
     */
    public function on(string $eventType, $calbackFunction)
    {
        $this->listeners[$eventType][] = $calbackFunction;
    }
}

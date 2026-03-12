<?php

namespace Infrastructure\Listeners;

use Illuminate\Http\Request;
use App\Models\AuthAuditLog;
use ReflectionClass;

final class AuthAuditLogger
{
    public function __construct(
        private readonly Request $request
    ) {
    }

    /**
     * Listen for all Domain Events and log them.
     */
    public function handle(object $event): void
    {
        $reflection = new ReflectionClass($event);
        $eventName = $reflection->getShortName();

        $userId = null;
        if (property_exists($event, 'userId')) {
            $userIdValue = $event->userId;
            $userId = is_object($userIdValue) && method_exists($userIdValue, 'value') 
                        ? $userIdValue->value() 
                        : (string)$userIdValue;
        }

        $payload = [];
        foreach ($reflection->getProperties() as $property) {
            $value = $property->getValue($event);
            if (is_object($value)) {
                if (method_exists($value, 'value')) {
                    $payload[$property->getName()] = $value->value();
                } elseif ($value instanceof \DateTimeInterface) {
                    $payload[$property->getName()] = $value->format(\DateTime::ATOM);
                } else {
                    $payload[$property->getName()] = (string) $value;
                }
            } else {
                $payload[$property->getName()] = $value;
            }
        }

        AuthAuditLog::create([
            'user_id' => $userId,
            'event_type' => $eventName,
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->request->userAgent(),
            'payload' => $payload,
            'created_at' => now(),
        ]);
    }
}

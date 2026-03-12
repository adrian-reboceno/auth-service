<?php

namespace Interfaces\Http\Controllers;

use Application\Auth\Login\LoginCommand;
use Application\Auth\Login\LoginCommandHandler;
use Application\Auth\Logout\LogoutCommand;
use Application\Auth\Logout\LogoutCommandHandler;
use Application\Auth\RefreshToken\RefreshTokenCommand;
use Application\Auth\RefreshToken\RefreshTokenCommandHandler;
use Application\Auth\RevokeUserSecurity\RevokeUserSecurityCommand;
use Application\Auth\RevokeUserSecurity\RevokeUserSecurityCommandHandler;
use Interfaces\Http\Requests\Auth\LoginRequest;
use Interfaces\Http\Requests\Auth\RefreshTokenRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Domain\Services\Exceptions\InvalidCredentialsException;
use Infrastructure\JWT\JwksBuilder;

final class AuthController
{
    public function login(
        LoginRequest $request,
        LoginCommandHandler $handler
    ): JsonResponse {
        try {
            $command = new LoginCommand(
                $request->validated('email'),
                $request->validated('password'),
                $request->ip(),
                $request->userAgent() ?? 'unknown'
            );

            $result = $handler->handle($command);

            return response()->json($result);
        } catch (InvalidCredentialsException $e) {
            return response()->json(['error' => 'invalid_credentials'], JsonResponse::HTTP_UNAUTHORIZED);
        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_FORBIDDEN);
        }
    }

    public function refresh(
        RefreshTokenRequest $request,
        RefreshTokenCommandHandler $handler
    ): JsonResponse {
        try {
            $command = new RefreshTokenCommand(
                $request->validated('refresh_token'),
                $request->ip(),
                $request->userAgent() ?? 'unknown'
            );

            $result = $handler->handle($command);

            return response()->json($result);
        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_UNAUTHORIZED);
        }
    }

    public function logout(
        Request $request,
        LogoutCommandHandler $handler
    ): JsonResponse {
        $refreshToken = $request->input('refresh_token', '');
        $jti = $request->attributes->get('jti', '');

        $command = new LogoutCommand($refreshToken, $jti);
        $handler->handle($command);

        return response()->json(['message' => 'logged_out']);
    }

    public function securityRevoke(
        Request $request,
        RevokeUserSecurityCommandHandler $handler
    ): JsonResponse {
        try {
            $userId = $request->attributes->get('user_id');
            if (!$userId) throw new \DomainException('unauthorized');

            $command = new RevokeUserSecurityCommand(new \Domain\User\UserId($userId));
            $handler->handle($command);

            return response()->json(['message' => 'security_revoked']);
        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
    }


    public function securityRevokeUser(
        Request $request,
        RevokeUserSecurityCommandHandler $handler,
        string $id
    ): JsonResponse {
        try {
            $command = new RevokeUserSecurityCommand(new \Domain\User\UserId($id));
            $handler->handle($command);
            return response()->json(["message" => "security_revoked"]);
        } catch (\DomainException $e) {
            return response()->json(["error" => $e->getMessage()], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function bulkSecurityRevoke(
        Request $request,
        \Application\Auth\BulkSecurityRevoke\BulkSecurityRevokeCommandHandler $handler
    ): JsonResponse {
        try {
            $command = new \Application\Auth\BulkSecurityRevoke\BulkSecurityRevokeCommand();
            $result = $handler->handle($command);
            return response()->json(["message" => "bulk_security_revoked", "users_revoked" => $result["users_revoked"]]);
        } catch (\DomainException $e) {
            return response()->json(["error" => $e->getMessage()], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user_id' => $request->attributes->get('user_id'),
            'roles' => $request->attributes->get('user_roles'),
            'permissions' => $request->attributes->get('user_permissions'),
            'jti' => $request->attributes->get('jti'),
        ]);
    }

    public function jwks(JwksBuilder $builder): JsonResponse
    {
        return response()
            ->json($builder->build())
            ->header('Cache-Control', 'public, max-age=3600');
    }
}

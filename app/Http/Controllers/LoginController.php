<?php

namespace App\Http\Controllers;

use App\Models\RefreshToken;
use App\Models\User;
use Firebase\Auth\Token\Exception\InvalidToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use JwtTokenHelper;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Laravel\Lumen\Routing\Controller as BaseController;

class LoginController extends BaseController
{
    public function login(Request $request)
    {
        if ($request->has('token')) {
            $clientToken = $request->input('token');

            $firebaseUser = $this->checkWithFirebase($clientToken);
            $localUser = $this->getLocalUserOrRegister($firebaseUser->id, $firebaseUser);

            $appId = 1;

            $tokenHelper = new JwtTokenHelper($firebaseUser->id, $appId);

            $refreshToken = $tokenHelper->getRefreshToken();
            $accessToken = $tokenHelper->getAccessToken();

            $this->storeRefreshToken($refreshToken, $firebaseUser->id, $appId);

            return response()->json([
                "access_token" => $accessToken,
                "refresh_token" => $refreshToken
            ], 200);
        }

        return response()->json([
            "message" => "Unauthorized"
        ], 401);
    }

    private function checkWithFirebase($clientToken)
    {
        try {
            $verifiedToken = Firebase::auth()->verifyIdToken($clientToken);
            $firebaseUserId = $verifiedToken->claims()->get('sub');
            return Firebase::auth()->getUser($firebaseUserId);
        } catch (InvalidToken $e) {
            return response()->json([
                "message" => "Invalid Token"
            ], 401);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                "message" => "Invalid Argument"
            ], 401);
        }
    }

    private function getLocalUserOrRegister($firebaseUser)
    {
        return User::where('oauth_uid', $firebaseUser->id)->firstOr(function () {
            return User::create([
                'oauth_provider' => 'google',
                'oauth_uid' => $firebaseUser->uid,
                   'email' => $firebaseUser->email,
                   'first_name' =>  $firebaseUser->displayName,
                   'last_name' => "last_name",
                   // 'gender' => ,
                //    'locale' => ,
                   'picture' => $firebaseUser->photoUrl ,
                //    'create' => ,
                //    'modified' => ,
                   'type' => 'user',

            ]);
        });
    }

    private function storeRefreshToken($refreshToken, $userId, $appId)
    {
        return RefreshToken::create([
            'refresh_token' => $refreshToken,
            'user_id' => $userId,
            'app_id' => $appId,
        ]);
    }

    public function reissueToken(Request $request)
    {
        $refreshToken = $request->input('token');

        if ($refreshToken == null)
            return response()->json(["message" => "Unauthenticated"], 401);
        $userId = Auth::user()->id;
        $appId = 1;

        $localRefreshToken = RefreshToken::where('user_id', $userId)
            ->where('app_id', $appId)
            ->first();

        if ($localRefreshToken == null)
            return response()->json(["message" => "Unauthenticated"], 401);

        $tokenHelper = new JwtTokenHelper($userId, $appId);

        $accessToken = $tokenHelper->getAccessToken();

        return response()->json([
            "accessToken" => $accessToken
        ]);
    }

    public function test()
    {
        $token = new RefreshToken;

        $token->refresh_token = "123";
        $token->user_id = "987";
        $token->app_id = 1;

        $token->save();

        return $token;
    }
}

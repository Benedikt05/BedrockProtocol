<?php

/*
 * This file is part of BedrockProtocol.
 * Copyright (C) 2014-2022 PocketMine Team <https://github.com/pmmp/BedrockProtocol>
 *
 * BedrockProtocol is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol\types\login\clientdata;

use pocketmine\network\mcpe\protocol\types\skin\PersonaPieceTintColor;
use pocketmine\network\mcpe\protocol\types\skin\PersonaSkinPiece;
use pocketmine\network\mcpe\protocol\types\skin\SkinAnimation;
use pocketmine\network\mcpe\protocol\types\skin\SkinData;
use pocketmine\network\mcpe\protocol\types\skin\SkinImage;
use Ramsey\Uuid\Uuid;
use function array_map;
use function base64_decode;

final class ClientDataToSkinDataHelper{

	private const PIECE_TYPE_MAP = [
		// UNKNOWN
		"unknown" => PersonaSkinPiece::PIECE_TYPE_UNKNOWN,
		"persona_unknown" => PersonaSkinPiece::PIECE_TYPE_UNKNOWN,
		// SKELETON
		"skeleton" => PersonaSkinPiece::PIECE_TYPE_SKELETON,
		"persona_skeleton" => PersonaSkinPiece::PIECE_TYPE_SKELETON,
		// BODY
		"body" => PersonaSkinPiece::PIECE_TYPE_BODY,
		"persona_body" => PersonaSkinPiece::PIECE_TYPE_BODY,
		// SKIN
		"skin" => PersonaSkinPiece::PIECE_TYPE_SKIN,
		"persona_skin" => PersonaSkinPiece::PIECE_TYPE_SKIN,
		// BOTTOM
		"bottom" => PersonaSkinPiece::PIECE_TYPE_BOTTOM,
		"persona_bottom" => PersonaSkinPiece::PIECE_TYPE_BOTTOM,
		// FEET
		"feet" => PersonaSkinPiece::PIECE_TYPE_FEET,
		"persona_feet" => PersonaSkinPiece::PIECE_TYPE_FEET,
		// DRESS
		"dress" => PersonaSkinPiece::PIECE_TYPE_DRESS,
		"persona_dress" => PersonaSkinPiece::PIECE_TYPE_DRESS,
		// TOP
		"top" => PersonaSkinPiece::PIECE_TYPE_TOP,
		"persona_top" => PersonaSkinPiece::PIECE_TYPE_TOP,
		// HIGH_PANTS
		"high_pants" => PersonaSkinPiece::PIECE_TYPE_HIGH_PANTS,
		"persona_high_pants" => PersonaSkinPiece::PIECE_TYPE_HIGH_PANTS,
		// HANDS
		"hands" => PersonaSkinPiece::PIECE_TYPE_HANDS,
		"persona_hand" => PersonaSkinPiece::PIECE_TYPE_HANDS,
		// OUTERWEAR
		"outerwear" => PersonaSkinPiece::PIECE_TYPE_OUTERWEAR,
		"persona_outerwear" => PersonaSkinPiece::PIECE_TYPE_OUTERWEAR,
		// FACIAL_HAIR
		"facialhair" => PersonaSkinPiece::PIECE_TYPE_FACIAL_HAIR,
		"persona_facial_hair" => PersonaSkinPiece::PIECE_TYPE_FACIAL_HAIR,
		// MOUTH
		"mouth" => PersonaSkinPiece::PIECE_TYPE_MOUTH,
		"persona_mouth" => PersonaSkinPiece::PIECE_TYPE_MOUTH,
		// EYES
		"eyes" => PersonaSkinPiece::PIECE_TYPE_EYES,
		"persona_eyes" => PersonaSkinPiece::PIECE_TYPE_EYES,
		// HAIR
		"hair" => PersonaSkinPiece::PIECE_TYPE_HAIR,
		"persona_hair" => PersonaSkinPiece::PIECE_TYPE_HAIR,
		// HOOD
		"hood" => PersonaSkinPiece::PIECE_TYPE_HOOD,
		"persona_hood" => PersonaSkinPiece::PIECE_TYPE_HOOD,
		// BACK
		"back" => PersonaSkinPiece::PIECE_TYPE_BACK,
		"persona_back" => PersonaSkinPiece::PIECE_TYPE_BACK,
		// FACE_ACCESSORY
		"faceaccessory" => PersonaSkinPiece::PIECE_TYPE_FACE_ACCESSORY,
		"persona_face_accessory" => PersonaSkinPiece::PIECE_TYPE_FACE_ACCESSORY,
		// HEAD
		"head" => PersonaSkinPiece::PIECE_TYPE_HEAD,
		"persona_head" => PersonaSkinPiece::PIECE_TYPE_HEAD,
		// LEGS
		"legs" => PersonaSkinPiece::PIECE_TYPE_LEGS,
		"persona_legs" => PersonaSkinPiece::PIECE_TYPE_LEGS,
		// LEFT_LEG
		"leftleg" => PersonaSkinPiece::PIECE_TYPE_LEFT_LEG,
		"persona_left_leg" => PersonaSkinPiece::PIECE_TYPE_LEFT_LEG,
		// RIGHT_LEG
		"rightleg" => PersonaSkinPiece::PIECE_TYPE_RIGHT_LEG,
		"persona_right_leg" => PersonaSkinPiece::PIECE_TYPE_RIGHT_LEG,
		// ARMS
		"arms" => PersonaSkinPiece::PIECE_TYPE_ARMS,
		"persona_arms" => PersonaSkinPiece::PIECE_TYPE_ARMS,
		// LEFT_ARM
		"leftarm" => PersonaSkinPiece::PIECE_TYPE_LEFT_ARM,
		"persona_left_arm" => PersonaSkinPiece::PIECE_TYPE_LEFT_ARM,
		// RIGHT_ARM
		"rightarm" => PersonaSkinPiece::PIECE_TYPE_RIGHT_ARM,
		"persona_right_arm" => PersonaSkinPiece::PIECE_TYPE_RIGHT_ARM,
		// CAPES
		"capes" => PersonaSkinPiece::PIECE_TYPE_CAPES,
		"persona_capes" => PersonaSkinPiece::PIECE_TYPE_CAPES,
		// CLASSIC_SKIN
		"classicskin" => PersonaSkinPiece::PIECE_TYPE_CLASSIC_SKIN,
		"persona_classic_skin" => PersonaSkinPiece::PIECE_TYPE_CLASSIC_SKIN,
		// EMOTE
		"emote" => PersonaSkinPiece::PIECE_TYPE_EMOTE,
		"persona_emote" => PersonaSkinPiece::PIECE_TYPE_EMOTE,
		// UNSUPPORTED
		"unsupported" => PersonaSkinPiece::PIECE_TYPE_UNSUPPORTED,
	];
	/**
	 * @throws \InvalidArgumentException
	 */
	private static function safeB64Decode(string $base64, string $context) : string{
		$result = base64_decode($base64, true);
		if($result === false){
			throw new \InvalidArgumentException("$context: Malformed base64, cannot be decoded");
		}
		return $result;
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	public static function fromClientData(ClientData $clientData) : SkinData{
		/** @var SkinAnimation[] $animations */
		$animations = [];
		foreach($clientData->AnimatedImageData as $k => $animation){
			$animations[] = new SkinAnimation(
				new SkinImage(
					$animation->ImageHeight,
					$animation->ImageWidth,
					self::safeB64Decode($animation->Image, "AnimatedImageData.$k.Image")
				),
				$animation->Type,
				$animation->Frames,
				$animation->AnimationExpression
			);
		}
		return new SkinData(
			$clientData->SkinId,
			"",
			self::safeB64Decode($clientData->SkinResourcePatch, "SkinResourcePatch"),
			new SkinImage($clientData->SkinImageHeight, $clientData->SkinImageWidth, self::safeB64Decode($clientData->SkinData, "SkinData")),
			$animations,
			new SkinImage($clientData->CapeImageHeight, $clientData->CapeImageWidth, self::safeB64Decode($clientData->CapeData, "CapeData")),
			self::safeB64Decode($clientData->SkinGeometryData, "SkinGeometryData"),
			self::safeB64Decode($clientData->SkinGeometryDataEngineVersion, "SkinGeometryDataEngineVersion"), //yes, they actually base64'd the version!
			self::safeB64Decode($clientData->SkinAnimationData, "SkinAnimationData"),
			$clientData->CapeId,
			null,
			self::convertArmSize($clientData->ArmSize),
			self::convertColor($clientData->SkinColor),
			array_map(function(ClientDataPersonaSkinPiece $piece) : PersonaSkinPiece{
				return new PersonaSkinPiece($piece->PieceId, self::convertPieceType($piece->PieceType), Uuid::fromString($piece->PackId), $piece->IsDefault, $piece->ProductId);
			}, $clientData->PersonaPieces),
			array_map(function(ClientDataPersonaPieceTintColor $tint) : PersonaPieceTintColor{
				$colors = [];
				foreach(array_slice(array_values($tint->Colors), 0, 4) as $color){
					$colors[] = self::convertColor($color);
				}
				while(count($colors) < 4){
					$colors[] = 0;
				}
				return new PersonaPieceTintColor($tint->PieceType, $colors);
			}, $clientData->PieceTintColors),
			true,
			$clientData->PremiumSkin,
			$clientData->PersonaSkin,
			$clientData->CapeOnClassicSkin,
			true, //assume this is true? there's no field for it ...
			$clientData->OverrideSkin ?? true,
		);
	}

	public static function convertArmSize(string $armSize) : int{
		return match ($armSize) {
			"slim" => SkinData::ARM_SIZE_SLIM,
			"wide", "" => SkinData::ARM_SIZE_WIDE,
			default => throw new \InvalidArgumentException("Unknown arm size $armSize")
		};
	}

	public static function convertColor(string $color) : int{
		$hex = ltrim($color, '#');
		if($hex === '' || $hex === '0'){
			return 0;
		}

		return (int) hexdec($hex);
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	private static function convertPieceType(string $pieceType) : int{
		$name = strtolower($pieceType);
		return self::PIECE_TYPE_MAP[$name] ?? throw new \InvalidArgumentException("Unknown persona piece type \"$pieceType\"");
	}
}

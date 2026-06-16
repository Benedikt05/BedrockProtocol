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

namespace pocketmine\network\mcpe\protocol;

use pocketmine\data\bedrock\BedrockDataFiles;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\utils\AssumptionFailedError;
use function array_flip;
use function file_get_contents;
use function is_array;
use function json_decode;

class LevelSoundEventPacket extends DataPacket implements ClientboundPacket, ServerboundPacket{
	public const NETWORK_ID = ProtocolInfo::LEVEL_SOUND_EVENT_PACKET;

	/** @see LevelSoundEvent */
	public int $sound;
	public Vector3 $position;
	public int $extraData = -1;
	public string $entityType = ":"; //???
	public bool $isBabyMob = false; //...
	public bool $disableRelativeVolume = false;
	public int $actorUniqueId = -1;
	public ?Vector3 $fireAtPosition = null;

	/** @var array<int, string> */
	private static array $idToStringMap;
	/** @var array<string, int> */
	private static array $stringToIdMap;
	/**
	 * @generate-create-func
	 */
	public static function create(
		int $sound,
		Vector3 $position,
		int $extraData,
		string $entityType,
		bool $isBabyMob,
		bool $disableRelativeVolume,
		int $actorUniqueId,
		//?Vector3 $fireAtPosition
	) : self{
		$result = new self;
		$result->sound = $sound;
		$result->position = $position;
		$result->extraData = $extraData;
		$result->entityType = $entityType;
		$result->isBabyMob = $isBabyMob;
		$result->disableRelativeVolume = $disableRelativeVolume;
		$result->actorUniqueId = $actorUniqueId;
		//$result->fireAtPosition = $fireAtPosition;
		return $result;
	}

	public static function nonActorSound(int $sound, Vector3 $position, bool $disableRelativeVolume, int $extraData = -1) : self{
		return self::create($sound, $position, $extraData, ":", false, $disableRelativeVolume, -1);
	}

	private static function makeSoundMap() : void{
		$map = json_decode(file_get_contents(BedrockDataFiles::LEVEL_SOUND_ID_MAP_JSON), true);
		if(!is_array($map)){
			throw new AssumptionFailedError("Invalid resource file format");
		}
		self::$idToStringMap = array_flip($map);
		self::$stringToIdMap = $map;
	}

	protected function decodePayload(PacketSerializer $in) : void{
		if(!isset(self::$idToStringMap)){
			self::makeSoundMap();
		}
		$this->sound = self::$stringToIdMap[$in->getString()] ?? -1;
		$this->position = $in->getVector3();
		$this->extraData = $in->getVarInt();
		$this->entityType = $in->getString();
		$this->isBabyMob = $in->getBool();
		$this->disableRelativeVolume = $in->getBool();
		$this->actorUniqueId = $in->getLLong(); //WHY IS THIS NON-STANDARD?
		$this->fireAtPosition = $in->readOptional(fn() => $in->getVector3());
	}

	protected function encodePayload(PacketSerializer $out) : void{
		if(!isset(self::$idToStringMap)){
			self::makeSoundMap();
		}
		$out->putString(self::$idToStringMap[$this->sound] ?? "");
		$out->putVector3($this->position);
		$out->putVarInt($this->extraData);
		$out->putString($this->entityType);
		$out->putBool($this->isBabyMob);
		$out->putBool($this->disableRelativeVolume);
		$out->putLLong($this->actorUniqueId);
		$out->writeOptional($this->fireAtPosition, fn($fireAtPosition) => $out->putVector3($fireAtPosition));
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleLevelSoundEvent($this);
	}
}

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

use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\network\mcpe\protocol\types\BossBarColor;

class BossEventPacket extends DataPacket implements ClientboundPacket, ServerboundPacket{
	public const NETWORK_ID = ProtocolInfo::BOSS_EVENT_PACKET;

	/** S2C: Shows the boss-bar to the player. */
	public const TYPE_SHOW = 0;
	/** C2S: Registers a player to a boss fight. */
	public const TYPE_REGISTER_PLAYER = 1;
	/** S2C: Removes the boss-bar from the client. */
	public const TYPE_HIDE = 2;
	/** C2S: Unregisters a player from a boss fight. */
	public const TYPE_UNREGISTER_PLAYER = 3;
	/** S2C: Sets the bar percentage. */
	public const TYPE_HEALTH_PERCENT = 4;
	/** S2C: Sets title of the bar. */
	public const TYPE_TITLE = 5;
	/** S2C: Updates misc properties of the bar and environment. */
	public const TYPE_PROPERTIES = 6;
	/** S2C: Updates boss-bar colour and overlay texture. */
	public const TYPE_TEXTURE = 7;
	/** C2S: Client asking the server to resend all boss data. */
	public const TYPE_QUERY = 8;

	public int $bossActorUniqueId;
	public int $eventType;

	public int $playerActorUniqueId;
	public float $healthPercent;
	public string $title;
	public string $filteredTitle;
	public int $color;
	public int $overlay;

	public static function create(
		int $bossActorUniqueId,
		int $eventId,
		int $playerActorUniqueId,
		float $healthPercent,
		string $title,
		int $color,
		int $overlay
	) : self{
		$result = new self;
		$result->bossActorUniqueId = $bossActorUniqueId;
		$result->eventType = $eventId;
		$result->playerActorUniqueId = $playerActorUniqueId;
		$result->healthPercent = $healthPercent;
		$result->title = $title;
		$result->filteredTitle = $title;
		$result->color = $color;
		$result->overlay = $overlay;
		return $result;
	}

	public static function show(int $bossActorUniqueId, string $title, float $healthPercent, bool $unused = false, int $color = BossBarColor::PURPLE, int $overlay = 0) : self{
		return self::create($bossActorUniqueId, self::TYPE_SHOW, 0, $healthPercent, $title, $color, $overlay);
	}

	public static function hide(int $bossActorUniqueId, int $playerActorUniqueId = 0, float $healthPercent = 1.0, string $title = "", int $color = BossBarColor::PURPLE, int $overlay = 0) : self{
		return self::create($bossActorUniqueId, self::TYPE_HIDE, $playerActorUniqueId, $healthPercent, $title, $color, $overlay);
	}

	public static function registerPlayer(int $bossActorUniqueId, int $playerActorUniqueId, float $healthPercent = 1.0, string $title = "", int $color = BossBarColor::PURPLE, int $overlay = 0) : self{
		return self::create($bossActorUniqueId, self::TYPE_REGISTER_PLAYER, $playerActorUniqueId, $healthPercent, $title, $color, $overlay);
	}

	public static function unregisterPlayer(int $bossActorUniqueId, int $playerActorUniqueId, float $healthPercent = 1.0, string $title = "", int $color = BossBarColor::PURPLE, int $overlay = 0) : self{
		return self::create($bossActorUniqueId, self::TYPE_UNREGISTER_PLAYER, $playerActorUniqueId, $healthPercent, $title, $color, $overlay);
	}

	public static function healthPercent(int $bossActorUniqueId, float $healthPercent, int $playerActorUniqueId = 0, string $title = "", int $color = BossBarColor::PURPLE, int $overlay = 0) : self{
		return self::create($bossActorUniqueId, self::TYPE_HEALTH_PERCENT, $playerActorUniqueId, $healthPercent, $title, $color, $overlay);
	}

	public static function title(int $bossActorUniqueId, string $title, int $playerActorUniqueId = 0, float $healthPercent = 1.0, int $color = BossBarColor::PURPLE, int $overlay = 0) : self{
		return self::create($bossActorUniqueId, self::TYPE_TITLE, $playerActorUniqueId, $healthPercent, $title, $color, $overlay);
	}

	public static function properties(int $bossActorUniqueId, bool $unused, int $color = BossBarColor::PURPLE, int $overlay = 0, int $playerActorUniqueId = 0, float $healthPercent = 1.0, string $title = "") : self{
		return self::create($bossActorUniqueId, self::TYPE_PROPERTIES, $playerActorUniqueId, $healthPercent, $title, $color, $overlay);
	}

	public static function query(int $bossActorUniqueId, int $playerActorUniqueId, float $healthPercent = 1.0, string $title = "", int $color = BossBarColor::PURPLE, int $overlay = 0) : self{
		return self::create($bossActorUniqueId, self::TYPE_QUERY, $playerActorUniqueId, $healthPercent, $title, $color, $overlay);
	}

	protected function decodePayload(PacketSerializer $in) : void{
		$this->bossActorUniqueId = $in->getActorUniqueId();
		$this->playerActorUniqueId = $in->getActorUniqueId();
		$this->eventType = $in->getUnsignedVarInt();
		$this->title = $in->getString();
		$this->filteredTitle = $in->getString();
		$this->healthPercent = $in->getLFloat();
		$this->color = $in->getByte();
		$this->overlay = $in->getByte();
	}

	protected function encodePayload(PacketSerializer $out) : void{
		$out->putActorUniqueId($this->bossActorUniqueId);
		$out->putActorUniqueId($this->playerActorUniqueId);
		$out->putUnsignedVarInt($this->eventType);
		$out->putString($this->title);
		$out->putString($this->filteredTitle);
		$out->putLFloat($this->healthPercent);
		$out->putByte($this->color);
		$out->putByte($this->overlay);
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleBossEvent($this);
	}
}

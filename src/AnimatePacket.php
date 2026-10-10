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

class AnimatePacket extends DataPacket implements ClientboundPacket, ServerboundPacket{
	public const NETWORK_ID = ProtocolInfo::ANIMATE_PACKET;

	public const ACTION_SWING_ARM = 1;

	public const ACTION_STOP_SLEEP = 3;
	public const ACTION_CRITICAL_HIT = 4;
	public const ACTION_MAGICAL_CRITICAL_HIT = 5;

	public int $action;
	public int $actorRuntimeId;
	public float $data = 0.0;
	public ?string $swingSource = null;
	public int $handSlot = 0;//main hand

	public static function create(int $actorRuntimeId, int $actionId, float $data = 0.0, ?string $swingSource = null, int $handSlot = 0) : self{
		$result = new self;
		$result->actorRuntimeId = $actorRuntimeId;
		$result->action = $actionId;
		$result->data = $data;
		$result->swingSource = $swingSource;
		$result->handSlot = $handSlot;
		return $result;
	}

	/** @deprecated */
	public static function boatHack(int $actorRuntimeId, int $actionId, float $rowingTime) : self{
		return self::create($actorRuntimeId, $actionId, 0, null, 0);
	}

	protected function decodePayload(PacketSerializer $in) : void{
		$this->action = $in->getByte();
		$this->actorRuntimeId = $in->getActorRuntimeId();
		$this->data = $in->getLFloat();
		$this->swingSource = $in->readOptional($in->getString(...));
		$this->handSlot = $in->getByte();
	}

	protected function encodePayload(PacketSerializer $out) : void{
		$out->putByte($this->action);
		$out->putActorRuntimeId($this->actorRuntimeId);
		$out->putLFloat($this->data);
		$out->writeOptional($this->swingSource, $out->putString(...));
		$out->putByte($this->handSlot);
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleAnimate($this);
	}
}

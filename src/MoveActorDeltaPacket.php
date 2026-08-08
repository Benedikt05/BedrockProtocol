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
use pocketmine\utils\BinaryDataException;

class MoveActorDeltaPacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::MOVE_ACTOR_DELTA_PACKET;

	public int $actorRuntimeId;
	public ?float $xPos = null;
	public ?float $yPos = null;
	public ?float $zPos = null;
	public ?float $xRot = null;
	public ?float $yRot = null;
	public ?float $zRot = null;
	public bool $onGround = false;
	public bool $teleport = false;
	public bool $forceMoveLocalEntity = false;
	public bool $forceCompletion = false;

	/**
	 * @throws BinaryDataException
	 */
	private function maybeReadCoord(int $flag, PacketSerializer $in) : ?float{
		if($in->getBool()){
			return $in->getLFloat();
		}
		return null;
	}

	/**
	 * @throws BinaryDataException
	 */
	private function maybeReadRotation(int $flag, PacketSerializer $in) : ?float{
		if($in->getBool()){
			return $in->getRotationByte();
		}
		return null;
	}

	protected function decodePayload(PacketSerializer $in) : void{
		$this->actorRuntimeId = $in->getActorRuntimeId();
		//TODO
	}

	private function maybeWriteCoord(?float $val, PacketSerializer $out) : void{
		$out->putBool($val !== null);
		if($val !== null){
			$out->putLFloat($val);
		}
	}

	private function maybeWriteRotation(?float $val, PacketSerializer $out) : void{
		$out->putBool($val !== null);
		if($val !== null){
			$out->putRotationByte($val);
		}
	}

	protected function encodePayload(PacketSerializer $out) : void{
		$out->putActorRuntimeId($this->actorRuntimeId);
		$this->maybeWriteCoord($this->xPos, $out);
		$this->maybeWriteCoord($this->yPos, $out);
		$this->maybeWriteCoord($this->zPos, $out);
		$this->maybeWriteRotation($this->xRot, $out);
		$this->maybeWriteRotation($this->yRot, $out);
		$this->maybeWriteRotation($this->zRot, $out);
		$out->putBool($this->onGround);
		$out->putBool($this->teleport);
		$out->putBool($this->forceMoveLocalEntity);
		$out->putBool($this->forceCompletion);
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleMoveActorDelta($this);
	}
}

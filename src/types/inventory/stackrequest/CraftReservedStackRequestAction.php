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

namespace pocketmine\network\mcpe\protocol\types\inventory\stackrequest;

use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\network\mcpe\protocol\types\GetTypeIdFromConstTrait;

/**
 * Spec name: ItemStackRequestReservedAction
 */
final class CraftReservedStackRequestAction extends ItemStackRequestAction{
	use GetTypeIdFromConstTrait;

	public const ID = ItemStackRequestActionType::CRAFTING_RESERVED;

	public function __construct(
		private string $reservedId,
		private int $numCrafts
	){}

	public function getReservedId() : string{ return $this->reservedId; }

	public function getNumCrafts() : int{ return $this->numCrafts; }

	public static function read(PacketSerializer $in) : self{
		$reservedId = $in->getString();
		$numCrafts = $in->getByte();
		return new self($reservedId, $numCrafts);
	}

	public function write(PacketSerializer $out) : void{
		$out->putString($this->reservedId);
		$out->putByte($this->numCrafts);
	}
}
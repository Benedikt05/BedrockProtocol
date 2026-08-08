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
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackExtraData;
use pocketmine\network\mcpe\protocol\types\recipe\RecipeIngredient;

final class ItemStackRequestNetworkItemInstanceDescriptor{
	/**
	 * @param string $rawExtraData Serialized ItemStackExtraData (use ItemStackExtraData->write())
	 * @see ItemStackExtraData::write()
	 */
	public function __construct(
		private RecipeIngredient $ingredient,
		private int $blockRuntimeId,
		private string $rawExtraData
	){}

	public function getIngredient() : RecipeIngredient{ return $this->ingredient; }

	public function getBlockRuntimeId() : int{ return $this->blockRuntimeId; }

	public function getRawExtraData() : string{ return $this->rawExtraData; }

	public static function read(PacketSerializer $in) : self{
		$ingredient = $in->getRecipeIngredient();
		$blockRuntimeId = $in->getUnsignedVarInt();
		$rawExtraData = $in->getString();
		return new self($ingredient, $blockRuntimeId, $rawExtraData);
	}

	public function write(PacketSerializer $out) : void{
		$out->putRecipeIngredient($this->ingredient);
		$out->putUnsignedVarInt($this->blockRuntimeId);
		$out->putString($this->rawExtraData);
	}
}
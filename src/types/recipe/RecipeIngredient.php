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

namespace pocketmine\network\mcpe\protocol\types\recipe;

use pocketmine\network\mcpe\protocol\PacketDecodeException;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;

final class RecipeIngredient{

	private const TYPE_NAME = "name";
	private const TYPE_MOLANG = "molang";
	private const TYPE_ITEM_TAG = "item_tag";

	private const EMPTY_AUX_VALUE = 0x7fff;

	public function __construct(
		private ?ItemDescriptor $descriptor,
		private int $count
	){}

	public function getDescriptor() : ?ItemDescriptor{
		return $this->descriptor;
	}

	public function getCount() : int{
		return $this->count;
	}

	/**
	 * Reads an ingredient in the format used by CraftingDataPacket, which differs from the one used everywhere else
	 * (see CommonTypes::getRecipeIngredient()).
	 *
	 * @throws PacketDecodeException
	 */
	public static function read(PacketSerializer $in) : self{
		$valid = $in->getVarInt();
		if($valid === 0){
			$in->getVarInt(); //aux value, always 0x7fff
			$count = $in->getVarInt();
			return new self(null, $count);
		}

		$type = $in->getString();
		$descriptor = match($type){
			self::TYPE_NAME => NameItemDescriptor::read($in),
			self::TYPE_MOLANG => MolangItemDescriptor::read($in),
			self::TYPE_ITEM_TAG => TagItemDescriptor::read($in),
			default => throw new PacketDecodeException("Unknown item descriptor type \"$type\"")
		};
		if($descriptor instanceof TagItemDescriptor){
			$in->getVarInt(); //aux value, always 0x7fff
		}
		$count = $in->getVarInt();

		return new self($descriptor, $count);
	}

	public function write(PacketSerializer $out) : void{
		$descriptor = $this->descriptor;
		if($descriptor === null){
			$out->putUnsignedVarInt(0);
			$out->putVarInt(self::EMPTY_AUX_VALUE);
			$out->putVarInt($this->count);
			return;
		}

		$out->putUnsignedVarInt(1);
		$out->putString(match(true){
			$descriptor instanceof NameItemDescriptor => self::TYPE_NAME,
			$descriptor instanceof MolangItemDescriptor => self::TYPE_MOLANG,
			$descriptor instanceof TagItemDescriptor => self::TYPE_ITEM_TAG,
			default => throw new \LogicException("Unknown item descriptor type " . get_class($descriptor))
		});
		$descriptor->write($out);
		if($descriptor instanceof TagItemDescriptor){
			$out->putVarInt(self::EMPTY_AUX_VALUE);
		}
		$out->putVarInt($this->count);
	}
}

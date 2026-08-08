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

use pocketmine\color\Color;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\types\DimensionIds;
use pocketmine\network\mcpe\protocol\types\MapDecoration;
use pocketmine\network\mcpe\protocol\types\MapImage;
use pocketmine\network\mcpe\protocol\types\MapTrackedObject;
use pocketmine\utils\Binary;

class ClientboundMapItemDataPacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::CLIENTBOUND_MAP_ITEM_DATA_PACKET;

	public int $mapId;
	public int $dimensionId = DimensionIds::OVERWORLD;
	public bool $isLocked = false;
	public BlockPosition $origin;

	/** @var int[]|null */
	public ?array $parentMapIds = null;
	public ?int $scale = null;

	/** @var MapTrackedObject[]|null */
	public ?array $trackedEntities = null;
	/** @var MapDecoration[]|null */
	public ?array $decorations = null;

	public ?int $xOffset = null;
	public ?int $yOffset = null;
	public ?MapImage $colors = null;

	protected function decodePayload(PacketSerializer $in) : void{
		$this->mapId = $in->getActorUniqueId();
		$this->dimensionId = $in->getByte();
		$this->isLocked = $in->getBool();
		$this->origin = $in->getBlockPosition();

		if($in->getBool()){
			$this->parentMapIds = [];
			$count = $in->getUnsignedVarInt();
			for($i = 0; $i < $count; ++$i){
				$this->parentMapIds[] = $in->getActorUniqueId();
			}
		}

		if($in->getBool()){
			$this->scale = $in->getByte();
		}

		if($in->getBool()){
			$this->trackedEntities = [];
			$count = $in->getUnsignedVarInt();
			for($i = 0; $i < $count; ++$i){
				$object = new MapTrackedObject();
				$object->type = $in->getLInt();
				if($object->type === MapTrackedObject::TYPE_BLOCK){
					$object->blockPosition = $in->getBlockPosition();
				}elseif($object->type === MapTrackedObject::TYPE_ENTITY){
					$object->actorUniqueId = $in->getActorUniqueId();
				}else{
					throw new PacketDecodeException("Unknown map object type $object->type");
				}
				$this->trackedEntities[] = $object;
			}
		}

		if($in->getBool()){
			$this->decorations = [];
			$count = $in->getUnsignedVarInt();
			for($i = 0; $i < $count; ++$i){
				$icon = $in->getByte();
				$rotation = $in->getByte();
				$xOffset = $in->getByte();
				$yOffset = $in->getByte();
				$label = $in->getString();
				$color = Color::fromRGBA(Binary::flipIntEndianness($in->getLInt()));
				$this->decorations[] = new MapDecoration($icon, $rotation, $xOffset, $yOffset, $label, $color);
			}
		}

		$width = $in->getBool() ? $in->getVarInt() : null;
		$height = $in->getBool() ? $in->getVarInt() : null;
		$this->xOffset = $in->getBool() ? $in->getVarInt() : null;
		$this->yOffset = $in->getBool() ? $in->getVarInt() : null;

		if($in->getBool()){
			if($width === null || $height === null){
				throw new PacketDecodeException("Expected width and height to be present");
			}
			$count = $in->getUnsignedVarInt();
			if($count !== $width * $height){
				throw new PacketDecodeException("Expected colour count of " . ($height * $width) . " (height $height * width $width), got $count");
			}

			$this->colors = MapImage::decode($in, $height, $width);
		}

		if($this->colors === null && ($this->xOffset !== null || $this->yOffset !== null)){
			throw new PacketDecodeException("Expected xOffset and yOffset to be null");
		}
	}

	protected function encodePayload(PacketSerializer $out) : void{
		$out->putActorUniqueId($this->mapId);
		$out->putByte($this->dimensionId);
		$out->putBool($this->isLocked);
		$out->putBlockPosition($this->origin);

		$out->putBool($this->parentMapIds !== null);
		if($this->parentMapIds !== null){
			$out->putUnsignedVarInt(count($this->parentMapIds));
			foreach($this->parentMapIds as $id){
				$out->putActorUniqueId($id);
			}
		}

		$out->putBool($this->scale !== null);
		if($this->scale !== null){
			$out->putByte($this->scale);
		}

		$out->putBool($this->trackedEntities !== null);
		if($this->trackedEntities !== null){
			$out->putUnsignedVarInt(count($this->trackedEntities));
			foreach($this->trackedEntities as $object){
				$out->putLInt($object->type);
				if($object->type === MapTrackedObject::TYPE_BLOCK){
					$out->putBlockPosition($object->blockPosition);
				}elseif($object->type === MapTrackedObject::TYPE_ENTITY){
					$out->putActorUniqueId($object->actorUniqueId);
				}else{
					throw new \InvalidArgumentException("Unknown map object type $object->type");
				}
			}
		}

		$out->putBool($this->decorations !== null);
		if($this->decorations !== null){
			$out->putUnsignedVarInt(count($this->decorations));
			foreach($this->decorations as $decoration){
				$out->putByte($decoration->getIcon());
				$out->putByte($decoration->getRotation());
				$out->putByte($decoration->getXOffset());
				$out->putByte($decoration->getYOffset());
				$out->putString($decoration->getLabel());
				$out->putLInt(Binary::flipIntEndianness($decoration->getColor()->toRGBA()));
			}
		}

		$colors = $this->colors;

		$out->putBool($colors !== null);
		if($colors !== null){
			$out->putVarInt($colors->getWidth());
		}

		$out->putBool($colors !== null);
		if($colors !== null){
			$out->putVarInt($colors->getHeight());
		}

		$out->putBool($this->xOffset !== null);
		if($this->xOffset !== null){
			$out->putVarInt($this->xOffset);
		}

		$out->putBool($this->yOffset !== null);
		if($this->yOffset !== null){
			$out->putVarInt($this->yOffset);
		}

		$out->putBool($colors !== null);
		if($colors !== null){
			$out->putUnsignedVarInt($colors->getWidth() * $colors->getHeight());
			$colors->encode($out);
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleClientboundMapItemData($this);
	}
}

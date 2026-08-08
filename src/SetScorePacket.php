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

use InvalidArgumentException;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use function count;

class SetScorePacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::SET_SCORE_PACKET;

	/** @var ScorePacketEntry[] */
	public array $entries = [];

	/**
	 * @generate-create-func
	 * @param ScorePacketEntry[] $entries
	 */
	public static function create(array $entries) : self{
		$result = new self;
		$result->entries = $entries;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void{
		for($i = 0, $i2 = $in->getUnsignedVarInt(); $i < $i2; ++$i){
			$entry = new ScorePacketEntry();
			$entry->type = $in->getUnsignedVarInt();
			$in->getString();
			$entry->scoreboardId = $in->getVarLong();

			switch($entry->type){
				case ScorePacketEntry::TYPE_REMOVE:
					$entry->objectiveName = $in->readOptional(fn() => $in->getString());
					break;
				case ScorePacketEntry::TYPE_PLAYER:
				case ScorePacketEntry::TYPE_ENTITY:
					$entry->objectiveName = $in->getString();
					$entry->score = $in->getLInt();
					$entry->actorUniqueId = $in->getActorUniqueId();
					break;
				case ScorePacketEntry::TYPE_FAKE_PLAYER:
					$entry->objectiveName = $in->getString();
					$entry->score = $in->getLInt();
					$entry->customName = $in->getString();
					break;
				default:
					throw new PacketDecodeException("Unknown entry type $entry->type");
			}
			$this->entries[] = $entry;
		}
	}

	protected function encodePayload(PacketSerializer $out) : void{
		$out->putUnsignedVarInt(count($this->entries));
		foreach($this->entries as $entry){
			$out->putUnsignedVarInt($entry->type);
			$out->putString(match ($entry->type) {
				ScorePacketEntry::TYPE_REMOVE => "remove",
				ScorePacketEntry::TYPE_PLAYER => "changeplayer",
				ScorePacketEntry::TYPE_ENTITY => "changeentity",
				ScorePacketEntry::TYPE_FAKE_PLAYER => "changefakeplayer",
				default => throw new InvalidArgumentException("Unknown type $entry->type")
			});
			$out->putVarLong($entry->scoreboardId);
			switch($entry->type){
				case ScorePacketEntry::TYPE_REMOVE:
					$out->writeOptional($entry->objectiveName, fn($objectiveName) => $out->putString($objectiveName));
					break;
				case ScorePacketEntry::TYPE_PLAYER:
				case ScorePacketEntry::TYPE_ENTITY:
					$out->putString($entry->objectiveName ?? throw new InvalidArgumentException("Objective name must be set for player/entity entry"));
					$out->putLInt($entry->score);
					$out->putActorUniqueId($entry->actorUniqueId);
					break;
				case ScorePacketEntry::TYPE_FAKE_PLAYER:
					$out->putString($entry->objectiveName ?? throw new InvalidArgumentException("Objective name must be set for fake player entry"));
					$out->putLInt($entry->score);
					$out->putString($entry->customName);
					break;
				default:
					throw new InvalidArgumentException("Unknown entry type $entry->type");
			}
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleSetScore($this);
	}
}

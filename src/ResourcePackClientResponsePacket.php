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
use function count;

class ResourcePackClientResponsePacket extends DataPacket implements ServerboundPacket{
	public const NETWORK_ID = ProtocolInfo::RESOURCE_PACK_CLIENT_RESPONSE_PACKET;

	public const STATUS_REFUSED = 0; //cancel
	public const STATUS_SEND_PACKS = 1; //downloading
	public const STATUS_HAVE_ALL_PACKS = 2; //downloadingfinished
	public const STATUS_COMPLETED = 3; //resourcepackstackfinished

	public int $status;
	/** @var string[] */
	public array $packIds = [];

	/**
	 * @generate-create-func
	 * @param string[] $packIds
	 */
	public static function create(int $status, array $packIds) : self{
		$result = new self;
		$result->status = $status;
		$result->packIds = $packIds;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void{
		$this->status = $in->getUnsignedVarInt();
		$in->getString(); //status/response string
		if($this->status === self::STATUS_SEND_PACKS){
			$entryCount = $in->getUnsignedVarInt();
			$this->packIds = [];
			while($entryCount-- > 0){
				$this->packIds[] = $in->getString();
			}
		}
	}

	protected function encodePayload(PacketSerializer $out) : void{
		$out->putByte($this->status);
		$out->putLShort(count($this->packIds));
		foreach($this->packIds as $id){
			$out->putString($id);
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleResourcePackClientResponse($this);
	}
}

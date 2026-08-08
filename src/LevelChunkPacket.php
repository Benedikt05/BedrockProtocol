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
use pocketmine\network\mcpe\protocol\types\ChunkPosition;
use pocketmine\network\mcpe\protocol\types\DimensionIds;
use function count;

class LevelChunkPacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::LEVEL_CHUNK_PACKET;

	private const MAX_BLOB_HASHES = 64;
	private ChunkPosition $chunkPosition;
	/** @phpstan-var DimensionIds::* */
	private int $dimensionId;
	private int $subChunkCount;
	private ?int $clientRequestSubChunkLimit = null;
	private bool $cacheEnabled;
	/** @var int[] */
	private array $usedBlobHashes = [];
	private string $extraPayload;

	/**
	 * @generate-create-func
	 *
	 * @param int[] $usedBlobHashes
	 */
	public static function create(
		ChunkPosition $chunkPosition,
		int $dimensionId,
		int $subChunkCount,
		?int $clientRequestSubChunkLimit,
		bool $cacheEnabled,
		array $usedBlobHashes,
		string $extraPayload
	): self{
		$result = new self;
		$result->chunkPosition = $chunkPosition;
		$result->dimensionId = $dimensionId;
		$result->subChunkCount = $subChunkCount;
		$result->clientRequestSubChunkLimit = $clientRequestSubChunkLimit;
		$result->cacheEnabled = $cacheEnabled;
		$result->usedBlobHashes = $usedBlobHashes;
		$result->extraPayload = $extraPayload;
		return $result;
	}

	public function getChunkPosition() : ChunkPosition{ return $this->chunkPosition; }

	public function getDimensionId() : int{
		return $this->dimensionId;
	}

	public function getSubChunkCount() : int{
		return $this->subChunkCount;
	}

	public function isClientSubChunkRequestEnabled() : bool{
		return $this->clientRequestSubChunkLimit !== null;
	}

	public function getClientRequestSubChunkLimit() : ?int{
		return $this->clientRequestSubChunkLimit;
	}

	public function isCacheEnabled() : bool{
		return $this->cacheEnabled;
	}

	/**
	 * @return int[]
	 */
	public function getUsedBlobHashes() : array{
		return $this->usedBlobHashes;
	}

	public function getExtraPayload() : string{
		return $this->extraPayload;
	}

	protected function decodePayload(PacketSerializer $in) : void{
		$this->chunkPosition = ChunkPosition::read($in);
		$this->dimensionId = $in->getVarInt();
		$this->subChunkCount = $in->getUnsignedVarInt();

		$this->clientRequestSubChunkLimit = $in->getBool() ? $in->getVarInt() : null;
		$this->cacheEnabled = $in->getBool();

		$this->usedBlobHashes = [];
		$count = $in->getUnsignedVarInt();
		if($count > self::MAX_BLOB_HASHES){
			throw new PacketDecodeException("Expected at most " . self::MAX_BLOB_HASHES . " blob hashes, got " . $count);
		}
		for($i = 0; $i < $count; ++$i){
			$this->usedBlobHashes[] = $in->getLLong();
		}

		$this->extraPayload = $in->getString();
	}

	protected function encodePayload(PacketSerializer $out) : void{
		$this->chunkPosition->write($out);
		$out->putVarInt($this->dimensionId);
		$out->putUnsignedVarInt($this->subChunkCount);

		$out->putBool($this->clientRequestSubChunkLimit !== null);
		if($this->clientRequestSubChunkLimit !== null){
			$out->putVarInt($this->clientRequestSubChunkLimit);
		}
		$out->putBool($this->cacheEnabled);

		$out->putUnsignedVarInt(count($this->usedBlobHashes));
		foreach($this->usedBlobHashes as $hash){
			$out->putLLong($hash);
		}

		$out->putString($this->extraPayload);
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleLevelChunk($this);
	}
}

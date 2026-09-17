<?php

declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol;

use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\network\mcpe\protocol\types\BlockPosition;

class RecordStartedPacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::RECORD_STARTED_PACKET;

	public BlockPosition $blockPosition;

	public int $serverSoundHandle;

	/**
	 * @generate-create-func
	 */
	public static function create(BlockPosition $blockPosition, int $serverSoundHandle) : self{
		$result = new self;
		$result->blockPosition = $blockPosition;
		$result->serverSoundHandle = $serverSoundHandle;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void{

	}

	protected function encodePayload(PacketSerializer $out) : void{
		$out->putBlockPosition($this->blockPosition);
		$out->putLLong($this->serverSoundHandle);
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleRecordStarted($this);
	}
}
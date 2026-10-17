<?php

declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol;

use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\network\mcpe\protocol\types\SoundData;

class ClientboundUpdateSoundDataPacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::CLIENTBOUND_UPDATE_SOUND_DATA_PACKET;

	public int $serverSoundHandle;
	public SoundData $event;

	public static function create(
		int $serverSoundHandle,
		SoundData $event,
	) : self{
		$result = new self;
		$result->serverSoundHandle = $serverSoundHandle;
		$result->event = $event;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void{

	}

	protected function encodePayload(PacketSerializer $out) : void{
		$out->putLLong($this->serverSoundHandle);
		$this->event->write($out);
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleClientboundUpdateSoundData($this);
	}
}
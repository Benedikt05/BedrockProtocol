<?php

declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol;

use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\network\mcpe\protocol\types\SoundData;

class ClientboundUpdateSoundDataPacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::CLIENTBOUND_UPDATE_SOUND_DATA_PACKET;

	public int $serverSoundHandle;
	public ?SoundData $stop = null;
	public ?SoundData $setVolume = null;
	public ?SoundData $setPitch = null;
	public ?SoundData $fade = null;
	public ?SoundData $seekTo = null;
	public ?SoundData $pause = null;
	public ?SoundData $resume = null;

	public static function create(
		int $serverSoundHandle,
		?SoundData $stop = null,
		?SoundData $setVolume = null,
		?SoundData $setPitch = null,
		?SoundData $fade = null,
		?SoundData $seekTo = null,
		?SoundData $pause = null,
		?SoundData $resume = null
	) : self{
		$result = new self;
		$result->serverSoundHandle = $serverSoundHandle;
		$result->stop = $stop;
		$result->setVolume = $setVolume;
		$result->setPitch = $setPitch;
		$result->fade = $fade;
		$result->seekTo = $seekTo;
		$result->pause = $pause;
		$result->resume = $resume;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void{

	}

	protected function encodePayload(PacketSerializer $out) : void{
		$out->putLLong($this->serverSoundHandle);
		$out->writeOptional($this->stop, fn(SoundData $data) => $data->write($out));
		$out->writeOptional($this->setVolume, fn(SoundData $data) => $data->write($out));
		$out->writeOptional($this->setPitch, fn(SoundData $data) => $data->write($out));
		$out->writeOptional($this->fade, fn(SoundData $data) => $data->write($out));
		$out->writeOptional($this->seekTo, fn(SoundData $data) => $data->write($out));
		$out->writeOptional($this->pause, fn(SoundData $data) => $data->write($out));
		$out->writeOptional($this->resume, fn(SoundData $data) => $data->write($out));
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleClientboundUpdateSoundData($this);
	}
}
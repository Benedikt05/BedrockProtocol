<?php

declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol;

use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;

class VoxelShapesPacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::VOXEL_SHAPES_PACKET;

	public static function create() : self{
		return new self;
	}

	protected function decodePayload(PacketSerializer $in) : void{

	}

	protected function encodePayload(PacketSerializer $out) : void{
		$out->putUnsignedVarInt(0);
		$out->putUnsignedVarInt(0);
		$out->putLShort(0);
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleVoxelShapes($this);
	}
}
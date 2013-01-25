﻿using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

using System.IO;
using System.Net.Sockets;

namespace MPLRServer
{
    class MSBExporter
    {
        class DumpPacket
        {
            public byte[] Data { get; set; }
            public bool Outboud { get; set; }
            public DateTime ArrivalTime { get; set; }
            public ushort Opcode { get; set; }

            public DumpPacket(MaplePacket pPacket)
            {
                pPacket.Reset();
                MaplePacket.CommunicationType type = (MaplePacket.CommunicationType)pPacket.ReadByte();
                Outboud = type == MaplePacket.CommunicationType.ClientPacket;

                if (type == MaplePacket.CommunicationType.Internal)
                {
                    ushort header = pPacket.ReadUShort();
                    Opcode = 0xEE00;
                    Opcode += (byte)(header & 0xFF);
                }
                else
                {
                    Opcode = pPacket.ReadUShort();
                }
                Data = new byte[pPacket.Length - 3];
                Buffer.BlockCopy(pPacket.ToArray(), 3, Data, 0, Data.Length); // byte + short (header)
                ArrivalTime = MasterThread.CurrentDate;
            }

            public byte[] Dump()
            {
                byte[] buffer = new byte[Data.Length + 13];
                ushort size = (ushort)(Data.Length);

                long ticks = ArrivalTime.Ticks;
                buffer[0] = (byte)ticks;
                buffer[1] = (byte)(ticks >> 8);
                buffer[2] = (byte)(ticks >> 16);
                buffer[3] = (byte)(ticks >> 24);
                buffer[4] = (byte)(ticks >> 32);
                buffer[5] = (byte)(ticks >> 40);
                buffer[6] = (byte)(ticks >> 48);
                buffer[7] = (byte)(ticks >> 56);

                buffer[8] = (byte)size;
                buffer[9] = (byte)(size >> 8);

                buffer[10] = (byte)Opcode;
                buffer[11] = (byte)(Opcode >> 8);

                buffer[12] = Outboud ? (byte)1 : (byte)0;

                Buffer.BlockCopy(Data, 0, buffer, 13, Data.Length);

                return buffer;
            }
        }

        private List<DumpPacket> _packets;

        public MSBExporter()
        {
            _packets = new List<DumpPacket>();
        }

        public void AddPacket(MaplePacket pPacket)
        {
            _packets.Add(new DumpPacket(pPacket));
        }

        public void Save(string pName, ushort pVersion)
        {
            using (FileStream stream = new FileStream(pName, FileMode.Create, FileAccess.Write))
            {
                BinaryWriter writer = new BinaryWriter(stream);
                writer.Write((ushort)0x2020);
                writer.Write("test123");
                writer.Write((ushort)6783);
                writer.Write("mc.craftnet.nl");
                writer.Write((ushort)8484);
                writer.Write((byte)0x08);
                writer.Write(pVersion);
                foreach (DumpPacket packet in _packets)
                {
                    writer.Write(packet.Dump());
                }
                stream.Flush();
            }
            _packets.Clear();
            _packets = null;
        }
    }
}
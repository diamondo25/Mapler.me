﻿using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace MPLRServer
{
    class GW_CharacterStat
    {
        public int ID { get; private set; }
        public string Name { get; private set; }

        public byte Gender { get; set; }
        public byte Skin { get; set; }
        public int Face { get; set; }
        public int Hair { get; set; }

        public long[] Pets { get; private set; }

        public byte Level { get; set; }

        public short JobID { get; set; }
        public short Str { get; set; }
        public short Dex { get; set; }
        public short Int { get; set; }
        public short Luk { get; set; }
        public int HP { get; set; }
        public int MaxHP { get; set; }
        public int MP { get; set; }
        public int MaxMP { get; set; }

        public short AP { get; set; }
        public short SP { get; set; }

        public List<KeyValuePair<byte, int>> SPData { get; private set; }

        public int EXP { get; set; }
        public int Fame { get; set; }

        public int GachaEXP { get; private set; }

        public int MapID { get; set; }
        public byte MapPos { get; set; }

        public int Unknown2 { get; private set; }
        public short JobSubID { get; private set; }

        public int DemonMark { get; private set; }
        public byte JobType { get; private set; }

        public int DateThing { get; private set; }

        public int Mesos { get; set; }

        public int[] Traits { get; private set; }
        public ushort[] TraitsToday { get; private set; }

        public int PVPExp { get; private set; }
        public byte PVPRank { get; private set; }
        public int BattlePoints { get; private set; }
        public byte BattleRank { get; private set; }

        public int HonourLevel { get; set; }
        public int HonourExp { get; set; }

        public GW_CharacterStat()
        {
            SPData = null;
        }

        public void Decode(MaplePacket pPacket)
        {
            this.ID = pPacket.ReadInt();
            this.Name = pPacket.ReadString(13);

            Logger.WriteLine("FOUND CHARACTER {0} (ID: {1})", this.Name, this.ID);

            this.Gender = pPacket.ReadByte();
            this.Skin = pPacket.ReadByte();
            this.Face = pPacket.ReadInt();
            this.Hair = pPacket.ReadInt();

            this.Pets = new long[3] { pPacket.ReadLong(), pPacket.ReadLong(), pPacket.ReadLong() };


            this.Level = pPacket.ReadByte();


            this.JobID = pPacket.ReadShort();
            this.Str = pPacket.ReadShort();
            this.Dex = pPacket.ReadShort();
            this.Int = pPacket.ReadShort();
            this.Luk = pPacket.ReadShort();
            this.HP = pPacket.ReadInt();
            this.MaxHP = pPacket.ReadInt();
            this.MP = pPacket.ReadInt();
            this.MaxMP = pPacket.ReadInt();

            this.AP = pPacket.ReadShort();

            SPData = new List<KeyValuePair<byte, int>>();
            if (GameHelper.IsExtendedSPJob(this.JobID))
            {
                byte amnt = pPacket.ReadByte();
                List<byte> haslist = new List<byte>();
                for (int j = 0; j < amnt; j++)
                {
                    byte v1 = pPacket.ReadByte(); // Job ID
                    int v2 = pPacket.ReadInt(); // Amount
                    SPData.Add(new KeyValuePair<byte, int>(v1, v2));

                    haslist.Add(v1);
                }
                for (byte j = 1; j < 20; j++)
                {
                    if (!haslist.Contains(j))
                        SPData.Add(new KeyValuePair<byte, int>(j, 0));
                }
            }
            else
            {
                SPData.Add(new KeyValuePair<byte, int>(0, pPacket.ReadShort()));
            }

            this.EXP = pPacket.ReadInt();
            this.Fame = pPacket.ReadInt();
            this.GachaEXP = pPacket.ReadInt();
            this.MapID = pPacket.ReadInt();
            this.MapPos = pPacket.ReadByte();

            this.Unknown2 = pPacket.ReadInt();
            this.JobSubID = pPacket.ReadShort();


            if (this.JobID / 100 == 31 || this.JobID == 3001)
            {
                this.DemonMark = pPacket.ReadInt();
            }

            //this.JobType = pPacket.ReadByte();
            pPacket.ReadByte(); // Fatigue ?

            this.DateThing = pPacket.ReadInt(); // YYYYMMDDhh

            this.Traits = new int[6] { 
                pPacket.ReadInt(), // Charisma
                pPacket.ReadInt(), // Insight
                pPacket.ReadInt(), // Willpower
                pPacket.ReadInt(), // Craft/Diligence
                pPacket.ReadInt(), // Empathy
                pPacket.ReadInt()  // Charm
            };

            this.TraitsToday = new ushort[6] { 
                pPacket.ReadUShort(), // Charisma
                pPacket.ReadUShort(), // Insight
                pPacket.ReadUShort(), // Willpower
                pPacket.ReadUShort(), // Craft/Diligence
                pPacket.ReadUShort(), // Empathy
                pPacket.ReadUShort()  // Charm
            };


            pPacket.Skip(9); // wat



            pPacket.ReadByte();
            pPacket.ReadInt();
            pPacket.ReadByte();
            pPacket.ReadInt();

            this.PVPExp = pPacket.ReadInt();
            this.PVPRank = pPacket.ReadByte();
            this.BattlePoints = pPacket.ReadInt();
            this.BattleRank = pPacket.ReadByte();


            pPacket.ReadInt();
            pPacket.ReadInt();
            pPacket.ReadByte();


            for (int j = 0; j < 6; j++)
            {
                pPacket.ReadInt();
                pPacket.ReadByte();
                pPacket.ReadInt();
            }

            pPacket.ReadInt();
            pPacket.ReadInt();
        }

        public void DecodeMesos(MaplePacket pPacket)
        {
            // THIS FUNCTION IS HAAARD
            this.Mesos = pPacket.ReadInt();
            // Phew, that's done...
        }
    }
}

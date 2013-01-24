﻿using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace Mapler_Client
{
    class ServerConnection : MESession
    {
        public static ServerConnection Instance { get; private set; }
        public static void Initialize()
        {
            string domain = "mc.craftnet.nl";
            if (System.IO.File.Exists("server.txt")) domain = System.IO.File.ReadAllText("server.txt");
            Instance = new ServerConnection(domain);
        }

        private List<ushort>[] _validHeaders;
        public List<string> AcceptedIPs { get; private set; }
        
        public ServerConnection(string pDomain)
            : base(pDomain, 23710)
        {
            AcceptedIPs = new List<string>();
        }

        public override void OnDisconnect()
        {
            if (!Program.Closing)
            {
                System.Windows.Forms.MessageBox.Show("Connection ended with the Mapler.me.\r\nThis application will now exit.", "Mapler.me error!");
                Environment.Exit(1);
            }
        }

        public override void OnPacket(MaplePacket pPacket)
        {
            byte type = pPacket.ReadByte();
            ushort header = pPacket.ReadUShort();
            Console.WriteLine(pPacket.ToString());
            if (type == (byte)MaplePacket.CommunicationType.Internal)
            {
                if (header == 0xFFFF)
                {
                    // Crypto
                    byte[] sendkey = pPacket.ReadBytes(32),
                        recvkey = pPacket.ReadBytes(32);

                    SetKeys(sendkey, recvkey);

                    _validHeaders = new List<ushort>[(byte)MaplePacket.CommunicationType.AMOUNT];
                    for (byte i = 0; i < (byte)MaplePacket.CommunicationType.AMOUNT; i++)
                    {
                        _validHeaders[i] = new List<ushort>();
                        for (ushort j = pPacket.ReadUShort(); j > 0; j--)
                        {
                            ushort tmp = pPacket.ReadUShort();
                            Logger.WriteLine("{0} accepts 0x{1:X4}", (MaplePacket.CommunicationType)i, tmp);
                            _validHeaders[i].Add(tmp);
                        }
                    }


                    for (byte j = pPacket.ReadByte(); j > 0; j--)
                    {
                        string ip = pPacket.ReadString();
                        AcceptedIPs.Add(ip);
                    }

                    Logger.WriteLine("Initialized keys and valid headers");
                }
                else if (header == 0xFFFE)
                {
                    // Create screenshot and send to server

                    string filename = System.IO.Path.GetTempFileName();

                    bool done = Screenshot.MakeScreenshotOfMaple(filename);
                    if (done)
                    {
                        using (MaplePacket packet = new MaplePacket(0xFFFE))
                        {
                            using (System.IO.BinaryReader br = new System.IO.BinaryReader(System.IO.File.OpenRead(filename)))
                            {
                                packet.WriteInt((int)br.BaseStream.Length);
                                packet.WriteBytes(br.ReadBytes((int)br.BaseStream.Length));
                            }
                            
                            packet.SwitchOver();

                            ForwardPacket(MaplePacket.CommunicationType.Internal, packet);
                        }
                    }
                }
            }

            pPacket.Dispose();
            pPacket = null;
        }

        public void ForwardPacket(MaplePacket.CommunicationType pType, MaplePacket pPacket)
        {
            pPacket.Reset();

            ushort header = pPacket.ReadUShort();

            if (!_validHeaders[(byte)pType].Contains(header))
            {
                return;
            }

            using (MaplePacket packet = new MaplePacket(pType, header))
            {
                packet.WriteBytes(pPacket.ReadLeftoverBytes());
                SendPacket(packet);
            }
        }
    }
}

﻿using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.Linq;
using System.Text;
using System.Windows.Forms;

namespace MaplerUpdater
{
    public partial class frmMain : Form
    {
        public static frmMain Instance { get; private set; }

        public frmMain()
        {
            InitializeComponent();
            Instance = this;
        }

        private void frmMain_Load(object sender, EventArgs e)
        {
        }

        bool check = false;
        private void frmMain_Shown(object sender, EventArgs e)
        {
            if (check) return;
            this.Refresh();
            check = true;
            new System.Threading.Thread(() =>
            {
                string ret = CraftNetTools.AppUpdates.Check(label1);
                if (ret == "Boot")
                {
                    System.Diagnostics.Process.Start("Mapler Client.exe", "/updated");
                    Environment.Exit(0);
                }
            }).Start();
        }

        private void label1_Click(object sender, EventArgs e)
        {

        }
    }
}

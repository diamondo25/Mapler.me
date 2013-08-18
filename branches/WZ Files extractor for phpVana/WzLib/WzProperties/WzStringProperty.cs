﻿using System.IO;
using MapleLib.WzLib.Util;

namespace MapleLib.WzLib.WzProperties
{
	/// <summary>
	/// A property with a string as a value
	/// </summary>
	public class WzStringProperty : IWzImageProperty
	{
		#region Fields
		internal string name, val;
		internal IWzObject parent;
		internal WzImage imgParent;
		#endregion

		#region Inherited Members
		public override object WzValue { get { return Value; } }
		/// <summary>
		/// The parent of the object
		/// </summary>
		public override IWzObject Parent { get { return parent; } internal set { parent = value; } }
		/// <summary>
		/// The image that this property is contained in
		/// </summary>
		public override WzImage ParentImage { get { return imgParent; } internal set { imgParent = value; } }
		/// <summary>
		/// The WzPropertyType of the property
		/// </summary>
		public override WzPropertyType PropertyType { get { return WzPropertyType.String; } }
		/// <summary>
		/// The name of the property
		/// </summary>
		public override string Name { get { return name; } set { name = value; } }
		public override void WriteValue(MapleLib.WzLib.Util.WzBinaryWriter writer)
		{
			writer.Write((byte)8);
			writer.WriteStringValue(Value, 0, 1);
		}
		public override void ExportXml(StreamWriter writer, int level)
		{
			writer.WriteLine(XmlUtil.Indentation(level) + XmlUtil.EmptyNamedValuePair("WzString", this.Name, this.Value));
		}
		/// <summary>
		/// Disposes the object
		/// </summary>
		public override void Dispose()
		{
			name = null;
			val = null;
		}
		#endregion

		#region Custom Members
		/// <summary>
		/// The value of the property
		/// </summary>
		public string Value { get { return val; } set { val = value; } }
		/// <summary>
		/// Creates a blank WzStringProperty
		/// </summary>
		public WzStringProperty() { }
		/// <summary>
		/// Creates a WzStringProperty with the specified name
		/// </summary>
		/// <param name="name">The name of the property</param>
		public WzStringProperty(string name)
		{
			this.name = name;
		}
		/// <summary>
		/// Creates a WzStringProperty with the specified name and value
		/// </summary>
		/// <param name="name">The name of the property</param>
		/// <param name="value">The value of the property</param>
		public WzStringProperty(string name, string value)
		{
			this.name = name;
			this.val = value;
		}
		#endregion
	}
}
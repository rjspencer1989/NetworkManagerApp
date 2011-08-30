package com.networkmanagerapp;

import java.util.HashMap;
import java.util.Map;
/**
 * @author rjs07u
 * Represents an item extracted from an XML file
 * itemData map contains the individual values from the XML within <item></item> 
 * Keys for data are the element names from the XML file 
 */
class XMLItem {
	private Map<String, String> itemData;
	public XMLItem(){
		this.itemData = new HashMap<String, String>();
	}
	
	/**
	 * Retrieve data for this item
	 * @return Map containing data for specific XML item instance
	 */
	public Map<String, String> getItemData() {
		return itemData;
	}
	
	/**
	 * Set the data for this item from the XML file
	 * @param itemData
	 */
	public void setItemData(final Map<String, String> itemData) {
		this.itemData = itemData;
	}
}

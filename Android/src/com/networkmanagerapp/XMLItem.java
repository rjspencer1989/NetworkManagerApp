/*
   Copyright 2011 Robert Spencer

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
 */

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

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

import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.util.ArrayList;
import java.util.List;

import javax.xml.parsers.ParserConfigurationException;
import javax.xml.parsers.SAXParserFactory;

import org.xml.sax.InputSource;
import org.xml.sax.SAXException;
import org.xml.sax.XMLReader;
/**
 * 
 * @author rjs07u
 * This class parses XML files and stores contents in XMLItem objects and creates an array of Strings containing item names
 */
public class XmlParser {
	
	/**
	 * @exception SAXException, ParserConfigurationException, FileNotFoundException, IOException, all handled internally
	 * @param filename. The name of the XML file to parse.
	 * @return XMLParsingResults object containing an arraylist of XMLItems and a string array of item names
	 */
	public XMLParsingResults returnParsedData(String filename){
		List<XMLItem> items = new ArrayList<XMLItem>();
		final XMLHandler myHandler = new XMLHandler();
		XMLReader xmlReader;
		try {
			xmlReader = SAXParserFactory.newInstance().newSAXParser().getXMLReader();
			xmlReader.setContentHandler(myHandler);
			FileInputStream fin = NetworkManagerMainActivity.getInstance().openFileInput(filename.substring(1));
			
			xmlReader.parse(new InputSource(fin));
			items = myHandler.getItemData();
		} catch (SAXException e) {
			e.printStackTrace();
		} catch (ParserConfigurationException e) {
			e.printStackTrace();
		} catch (FileNotFoundException e) {
			e.printStackTrace();
		} catch (IOException e) {
			e.printStackTrace();
		}
		final ArrayList<String> itemArrayList = new ArrayList<String>(items.size());
		for (int i = 0; i <  items.size(); i++) {
			itemArrayList.add(items.get(i).getItemData().get("name"));
		}
		String[] names = new String[itemArrayList.size()];
		itemArrayList.toArray(names);
		return new XMLParsingResults(names, items);
	}
}

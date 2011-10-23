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

import java.io.BufferedReader;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.io.InputStreamReader;
import java.util.ArrayList;
import java.util.Iterator;
import java.util.List;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;
import org.xml.sax.SAXException;
/**
 * 
 * @author rjs07u
 * This class parses XML files and stores contents in XMLItem objects and creates an array of Strings containing item names
 */
public class JSONParser {
	
	/**
	 * @exception SAXException, ParserConfigurationException, FileNotFoundException, IOException, all handled internally
	 * @param filename. The name of the XML file to parse.
	 * @return JSONParsingResults object containing an arraylist of XMLItems and a string array of item names
	 */
	public JSONParsingResults returnParsedData(String filename){
		List<JSONItem> items = new ArrayList<JSONItem>();
		try {
			FileInputStream fin = NetworkManagerMainActivity.getInstance().openFileInput(filename.substring(1));
			BufferedReader reader = new BufferedReader(new InputStreamReader(fin));
			String line;
			StringBuilder builder = new StringBuilder();
			while((line = reader.readLine())!=null){
				builder.append(line);
			}
			
			JSONArray ja = new JSONArray(builder.toString());
			for (int i = 0; i < ja.length(); i++){
				JSONObject jo = (JSONObject) ja.get(i);
				JSONItem item = new JSONItem();
				Iterator it = jo.keys();
				while(it.hasNext()){
					String name = it.next().toString();
					item.getItemData().put(name, jo.getString(name));
				}
				
				items.add(item);
			}
		} catch (FileNotFoundException e) {
			e.printStackTrace();
		} catch (IOException e){
			e.printStackTrace();
		} catch (JSONException e) {
			e.printStackTrace();
		}
		final ArrayList<String> itemArrayList = new ArrayList<String>(items.size());
		for (int i = 0; i <  items.size(); i++) {
			itemArrayList.add(items.get(i).getItemData().get("name"));
		}
		String[] names = new String[itemArrayList.size()];
		itemArrayList.toArray(names);
		return new JSONParsingResults(names, items);
	}
}

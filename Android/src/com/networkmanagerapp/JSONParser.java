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
import java.util.Map.Entry;
import java.util.Set;

import com.google.gson.JsonArray;
import com.google.gson.JsonElement;
import com.google.gson.JsonObject;
import com.google.gson.JsonParser;
/**
 * 
 * @author rjs07u
 * This class parses JSON files and stores contents in JSONItem objects and creates an array of Strings containing item names
 */
public class JSONParser {
	
	/**
	 * @exception FileNotFoundException, IOException, all handled internally
	 * @param filename. The name of the JSON file to parse.
	 * @return JSONParsingResults object containing an arraylist of JSONItems and a string array of item names
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

			JsonElement root = new JsonParser().parse(builder.toString());
			JsonArray arr = root.getAsJsonArray();
			for (int i = 0; i < arr.size(); i++){
				JsonObject jo = arr.get(i).getAsJsonObject();
				Set<Entry<String, JsonElement>> entry = jo.entrySet();
				JSONItem item = new JSONItem();
				Iterator<Entry<String, JsonElement>> iterator = entry.iterator();
				while(iterator.hasNext()){
					Entry<String, JsonElement> next = iterator.next();
					item.getItemData().put(next.getKey(), next.getValue().toString());
				}
				
				items.add(item);
			}	
		} catch (FileNotFoundException e) {
			e.printStackTrace();
		} catch (IOException e){
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

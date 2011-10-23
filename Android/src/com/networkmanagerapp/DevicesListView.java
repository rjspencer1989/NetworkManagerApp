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

import java.io.File;
import java.util.Date;
import java.util.List;

import android.app.ListActivity;
import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.content.IntentFilter;
import android.os.Bundle;
import android.view.View;
import android.widget.ArrayAdapter;
import android.widget.ListView;
import android.widget.TextView;

public class DevicesListView extends ListActivity {
	private static final int DEVICE_VIEW = 0;
	private List<JSONItem> items;
	private static DevicesListView instance;
	private DataReceiver receiver;
	private TextView lastRefresh;
	
	public List<JSONItem> getItems() {
		return items;
	}

	public void setItems(List<JSONItem> arrayList) {
		this.items = arrayList;
	}

	public static DevicesListView getInstance() {
		return instance;
	}

	/**
	 * Called when the activity is created
	 * Loads the UI from XML and matches elements to variables
	 * @param savedState used to reload data when orientation changes
	 */
	@Override
	protected void onCreate(final Bundle savedState) {
		super.onCreate(savedState);
		instance = this;
		getData();
		setContentView(R.layout.item_list);
		lastRefresh=(TextView)findViewById(R.id.list_modified);
	}

	/**
	 * Called whenever a list item is selected
	 * Passes data to the detail view to specify the details of the current device
	 * @param listView the list view that the item was selected from
	 * @param view the view that displays the content of the list view
	 * @param position the position in the list view
	 * @param idVal unique ID	
	 */
	@Override
	protected void onListItemClick(final ListView listView, final View view, final int position, final long idVal) {
		super.onListItemClick(listView, view, position, idVal);
		final Intent intent = new Intent(this, DeviceView.class);
		intent.putExtra("DEVICE_NAME", items.get(position).getItemData().get("name"));
		intent.putExtra("DEVICE_MAC_ADDRESS", items.get(position).getItemData().get("macAddress"));
		intent.putExtra("DEVICE_IP_ADDRESS", items.get(position).getItemData().get("ipAddress"));
		intent.putExtra("DEVICE_DNS", items.get(position).getItemData().get("dns"));
		startActivityForResult(intent, DEVICE_VIEW);	
	}
	
	/**
	 * Request new data from the router
	 */
	protected void getData() {		
		Intent i = new Intent(this, JSONBackgroundDownloaderService.class);
		i.putExtra("FILENAME", "/cgi-bin/devices.sh");
		i.putExtra("JSONFILE", "/devices.json");
		startService(i);
	}
	
	/**
	 * Called when the activity is hidden
	 * Stops listening for broadcast messages from the system
	 */
	@Override
	protected void onPause() {
		unregisterReceiver(receiver);
		super.onPause();
	}

	/**
	 * Called whenever the activity is brought to the front
	 * Registers to receive messages that there is new data available 
	 */
	@Override
	protected void onResume() {
		IntentFilter filter = new IntentFilter(JSONBackgroundDownloaderService.NEW_DATA_AVAILABLE);
		receiver = new DataReceiver();
		registerReceiver(receiver, filter);
		updateList();
		super.onResume();
	}

	/**
	 * Updates the list of connected devices
	 */
	protected void updateList(){
		JSONParsingResults results = new JSONParser().returnParsedData("/devices.json");
		setItems(results.getItem());
		setListAdapter(new ArrayAdapter<String>(this, android.R.layout.simple_list_item_1, results.getNames()));
		lastRefresh.setText("Refreshed on: " + new Date(new File(getFilesDir().toString() + "/devices.xml").lastModified()).toString());
	}

	/**
	 * This class notifies the activity that new data is available
	 * @author rob
	 *
	 */
	class DataReceiver extends BroadcastReceiver{
		/**
		 * Method is called whenever new data is received.
		 * @param arg0 the context of the application - not used
		 * @param arg1 the intent to update - not used
		 */
		@Override
		public void onReceive(Context arg0, Intent arg1) {
			updateList();
		}
	}
}
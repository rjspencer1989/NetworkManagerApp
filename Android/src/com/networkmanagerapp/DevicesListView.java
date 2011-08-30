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
	private List<XMLItem> items;
	private static DevicesListView instance;
	private DataReceiver receiver;
	private TextView lastRefresh;
	
	public List<XMLItem> getItems() {
		return items;
	}

	public void setItems(List<XMLItem> arrayList) {
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
		Intent i = new Intent(this, XMLBackgroundDownloaderService.class);
		i.putExtra("FILENAME", "/cgi-bin/devices.sh");
		i.putExtra("XMLFILE", "/devices.xml");
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
		IntentFilter filter = new IntentFilter(XMLBackgroundDownloaderService.NEW_DATA_AVAILABLE);
		receiver = new DataReceiver();
		registerReceiver(receiver, filter);
		updateList();
		super.onResume();
	}

	/**
	 * Updates the list of connected devices
	 */
	protected void updateList(){
		XMLParsingResults results = new XmlParser().returnParsedData("/devices.xml");
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
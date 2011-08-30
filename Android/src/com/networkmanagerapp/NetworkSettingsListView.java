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

/**
 * This class is a subclass of ListActivity, that creates a list of items
 * @author rjs07u
 * uses res/layout/item_list.xml
 */
public class NetworkSettingsListView extends ListActivity {
	private static NetworkSettingsListView instance;
	private List<XMLItem> items;
	private DataReceiver receiver;
	private TextView lastRefresh;
	
	public List<XMLItem> getItems() {
		return items;
	}

	public void setItems(List<XMLItem> items) {
		this.items = items;
	}
	
	public static NetworkSettingsListView getInstance() {
		return instance;
}

	/**
	 * Called when the activity is created.
	 * Loads the UI resource, and links variables to components
	 */
	@Override
	protected void onCreate(final Bundle savedState) {
		super.onCreate(savedState);
		instance = this;
		setContentView(R.layout.item_list);
		lastRefresh = (TextView)findViewById(R.id.list_modified);
		getData();
	}
	
	/**
	 * Called when an item is selected in the list.
	 * sends the currently selected setting name and value in the intent to load the 
	 * NetworkSettingView activity.
	 */
	@Override
	protected void onListItemClick(final ListView listView, final View view, final int position, final long idVal) {
		super.onListItemClick(listView, view, position, idVal);
		final Intent intent = new Intent(this, NetworkSettingView.class);
		intent.putExtra("LABEL", items.get(position).getItemData().get("name"));
		intent.putExtra("VALUE", items.get(position).getItemData().get("value"));
		startActivity(intent);	
	}

	/**
	 * Called to load new data into the List.
	 * @see XMLBackgroundDownloader
	 */
	protected void getData() {		
		Intent i = new Intent(this, XMLBackgroundDownloaderService.class);
		i.putExtra("FILENAME", "/cgi-bin/networkSettings.sh");
		i.putExtra("XMLFILE", "/networkSettings.xml");
		startService(i);
	}
	/**
	 * Called whenever the activity is no longer the foreground activity
	 * stops listening to broadcast messages
	 */
	@Override
	protected void onPause() {
		unregisterReceiver(receiver);
		super.onPause();
	}
	
	/**
	 * Called whenever the activity moves to the foreground.
	 * Registers a listener for Broadcast Messages that new data is available
	 */
	@Override
	protected void onResume() {
		IntentFilter filter = new IntentFilter(XMLBackgroundDownloaderService.NEW_DATA_AVAILABLE);
		receiver = new DataReceiver();
		registerReceiver(receiver, filter);
		super.onResume();
	}

	/**
	 * Updates the list with new data from the parsed data results.
	 */
	protected void updateList(){
		XMLParsingResults results = new XmlParser().returnParsedData("/networkSettings.xml");
		setItems(results.getItem());
		setListAdapter(new ArrayAdapter<String>(this, android.R.layout.simple_list_item_1, results.getNames()));
		lastRefresh.setText("Refreshed on: " + new Date(new File(getFilesDir().toString() + "/networkSettings.xml").lastModified()).toString());
	}

	/**
	 * Receives broadcast messages.
	 * When messages are received, the list is updated.
	 * @author rjs07u
	 *
	 */
	class DataReceiver extends BroadcastReceiver{
		@Override
		public void onReceive(Context arg0, Intent arg1) {
			updateList();
		}
	}
}
package com.networkmanagerapp;

import android.app.TabActivity;
import android.content.Intent;
import android.content.SharedPreferences;
import android.content.SharedPreferences.Editor;
import android.os.Bundle;
import android.preference.PreferenceManager;
import android.view.Menu;
import android.view.MenuInflater;
import android.view.MenuItem;
import android.widget.TabHost;

/**
 * The main class of the system. Contains the tab view.
 * @author rjs07u
 * uses res/layout/main.xml
 *
 */
public class NetworkManagerMainActivity extends TabActivity {
	private static final int DEVICE_TAB_INDEX = 0;
	private static final int NETWORK_SETTINGS_TAB_INDEX = 1;
	private static final int WEATHERMAP_TAB_INDEX = 2;
	private static final int NEW_PASSWORD_INTENT = 3;
	private static final int EXISTING_PASSWORD_INTENT = 4;
	private boolean isAuthenticated = false;
	TabHost tabHost;
	private static NetworkManagerMainActivity instance;

	public static NetworkManagerMainActivity getInstance() {
		return instance;
	}

	/**
	 * Get data from another activity when it completes.
	 * Used to check that the user has logged in successfully, and if not to quit.
	 * @param requestCode The code used when starting the activity
	 * @param resultCode Code used to signify the result of the activity
	 * @data data passed back to this activity from the activity that finished
	 */
	@Override
	protected void onActivityResult(int requestCode, int resultCode, Intent data) {
		super.onActivityResult(requestCode, resultCode, data);
		switch (resultCode) {
		case RESULT_OK:
			isAuthenticated = true;
			SharedPreferences prefs = PreferenceManager
					.getDefaultSharedPreferences(this);
			Editor edit = prefs.edit();
			edit.putBoolean("AUTHENTICATED", isAuthenticated);
			edit.commit();
			return;
		default:
			finish();
		}
	}

	/**
	 * Called whenever the activity moves into the background. Used to save data.
	 * Saves whether the user is authenticated or not
	 */
	@Override
	protected void onPause() {
		super.onPause();
		SharedPreferences prefs = PreferenceManager
				.getDefaultSharedPreferences(this);
		Editor edit = prefs.edit();
		edit.putBoolean("AUTHENTICATED", isAuthenticated);
		edit.commit();
	}

	/**
	 * Called when the activity is brought to the front. 
	 * Reloads any saved data.
	 * In this instance reloads the authentication status from preferences.
	 */
	@Override
	protected void onResume() {
		super.onResume();
		SharedPreferences prefs = PreferenceManager
				.getDefaultSharedPreferences(this);
		isAuthenticated = prefs.getBoolean("AUTHENTICATED", false);
	}

	/** 
	 * Called when the activity is first created. 
	 * sets up the UI and ensures the user is authenticated.
	 * Creates the tabs.
	 * @param savedInstanceState retrieve settings stored when the orientation changed
	 */
	@Override
	public void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.main);
		isAuthenticated = PreferenceManager.getDefaultSharedPreferences(this)
				.getBoolean("AUTHENTICATED", false);
		if (!isAuthenticated) {
			SharedPreferences prefs = PreferenceManager
					.getDefaultSharedPreferences(this);
			String password = prefs.getString("app_password_preference", "");
			if (password.equals("")) {
				Intent newPasswordIntent = new Intent(this,
						NewAppPassword.class);
				startActivityForResult(newPasswordIntent, NEW_PASSWORD_INTENT);
			} else {
				Intent loginIntent = new Intent(this, ExistingAppPassword.class);
				loginIntent.putExtra("PASSWORD", password);
				startActivityForResult(loginIntent, EXISTING_PASSWORD_INTENT);
			}
		}

		instance = this;
		tabHost = getTabHost(); // The activity TabHost
		TabHost.TabSpec deviceSpec, settingsSpec, weathermapSpec;
		Intent deviceIntent, settingsIntent, weathermapIntent;

		// Create an Intent to launch an Activity for the tab (to be reused)
		deviceIntent = new Intent().setClass(this, DevicesListView.class);
		deviceSpec = tabHost.newTabSpec("devices").setIndicator("Devices",
				getResources().getDrawable(android.R.drawable.ic_menu_call));
		deviceSpec.setContent(deviceIntent);
		tabHost.addTab(deviceSpec);

		settingsIntent = new Intent().setClass(this,
				NetworkSettingsListView.class);
		settingsSpec = tabHost.newTabSpec("networksettings").setIndicator(
				"Settings",
				getResources().getDrawable(
						android.R.drawable.ic_menu_preferences));
		settingsSpec.setContent(settingsIntent);
		tabHost.addTab(settingsSpec);

		weathermapIntent = new Intent().setClass(this, WeathermapViewer.class);
		weathermapSpec = tabHost.newTabSpec("weathermap").setIndicator(
				"Overview",
				getResources().getDrawable(android.R.drawable.ic_menu_mapmode));
		weathermapSpec.setContent(weathermapIntent);
		tabHost.addTab(weathermapSpec);

		tabHost.setCurrentTab(0);
	}

	/**
	 * Create the specified menu with options from res/menu/config_menu.xml
	 * @param the menu to create
	 * @return boolean value indicating successful creation of the menu
	 */
	@Override
	public boolean onCreateOptionsMenu(Menu menu) {
		MenuInflater inflater = getMenuInflater();
		inflater.inflate(R.menu.config_menu, menu);
		return super.onCreateOptionsMenu(menu);
	}

	/**
	 * Called whenever a menu item is selected.
	 * Based on the selected item id, the appropriate action is taken.
	 * @param item The item that has been selected.
	 * @return boolean indicating whether the event has been successfully handled
	 */
	@Override
	public boolean onOptionsItemSelected(MenuItem item) {
		switch (item.getItemId()) {
		case R.id.settings_menu:
			startActivity(new Intent(this, Preferences.class));
			return true;
		case R.id.refresh_data:
			if (tabHost.getCurrentTab() == DEVICE_TAB_INDEX) {
				DevicesListView.getInstance().getData();
			} else if (tabHost.getCurrentTab() == NETWORK_SETTINGS_TAB_INDEX) {
				NetworkSettingsListView.getInstance().getData();
			} else if (tabHost.getCurrentTab() == WEATHERMAP_TAB_INDEX) {
				WeathermapViewer.getInstance().getData();
			}
			return true;
		case R.id.reboot_router:
			Intent i = new Intent(this, RestartWifi.class);
			startService(i);
			return true;
		case R.id.exit:
			isAuthenticated = false;
			Editor edit = PreferenceManager.getDefaultSharedPreferences(this).edit();
			edit.putBoolean("AUTHENTICATED", isAuthenticated);
			edit.commit();
			finish();
			return true;
		default:
			return super.onOptionsItemSelected(item);
		}
	}
}
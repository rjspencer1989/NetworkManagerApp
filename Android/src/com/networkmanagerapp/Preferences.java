package com.networkmanagerapp;

import android.os.Bundle;
import android.preference.PreferenceActivity;

/**
 * Creates a UI for managing DefaultSharedPreferences
 * @author rjs07u
 * Uses res/xml/preferences.xml to define preferences to display
 *
 */
public class Preferences extends PreferenceActivity {
	/**
	 * Called when the activity is first created.
	 * Defines the location where preferences are stored.
	 * @param savedInstanceState used to retrieve settings that should be persistent across
	 * orientation changes
	 */
	@Override
	protected void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		addPreferencesFromResource(R.xml.preferences);
	}
}

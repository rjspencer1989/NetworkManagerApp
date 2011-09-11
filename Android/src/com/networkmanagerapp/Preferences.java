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

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

import android.app.Activity;
import android.app.AlertDialog;
import android.content.DialogInterface;
import android.content.SharedPreferences;
import android.content.SharedPreferences.Editor;
import android.os.Bundle;
import android.preference.PreferenceManager;
import android.view.View;
import android.widget.EditText;

/**
 * Activity class to allow users to set a password for the application if none is set.
 * @author rjs07u
 * uses res/layout/password_dialog_new.xml
 *
 */
public class NewAppPassword extends Activity {
	EditText first, second;
	/**
	 * Called when the activity is first created
	 * Defines the User interface and links UI components to variables.
	 * @param savedInstanceState loads data that was stored for an orientation change
	 */
	@Override
	protected void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.password_dialog_new);
		first = (EditText)findViewById(R.id.new_app_password);
		second = (EditText)findViewById(R.id.new_app_password_repeat);
	}

	/**
	 * Called whenever the ok button is pressed. 
	 * @param v The view that called the method
	 */
	public void ok_onclick(View v){
		if(first.getText().toString().equals(second.getText().toString()) && first.getText().length() > 0){
			SharedPreferences prefs = PreferenceManager.getDefaultSharedPreferences(this);
			Editor edit = prefs.edit();
			edit.putString("app_password_preference", first.getText().toString());
			edit.commit();
			setResult(RESULT_OK);
			finish();
		} else{
			first.setText("");
			second.setText("");
			
			AlertDialog.Builder builder = new AlertDialog.Builder(this);
			builder.setTitle("Invalid Password");
			builder.setMessage("The passwords must match and cannot be empty");
			builder.setPositiveButton("OK", new DialogInterface.OnClickListener() {
				
				@Override
				public void onClick(DialogInterface dialog, int which) {
					dialog.dismiss();
				}
			});
			builder.create().show();
		}
	}
	
	/**
	 * Called whenever the cancel button is pressed.
	 * @param v The view that called the method
	 */
	public void cancel_onclick(View v){
		setResult(RESULT_CANCELED);
		finish();
	}
}

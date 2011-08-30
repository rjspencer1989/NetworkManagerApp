package com.networkmanagerapp;

import android.app.Activity;
import android.app.AlertDialog;
import android.content.DialogInterface;
import android.os.Bundle;
import android.view.View;
import android.widget.EditText;

/**
 * This window allows the user to login to be able to use the app
 * The password is stored in the default shared preference.
 * Uses res/layout/password_dialog_existing.xml
 * @author rjs07u
 *
 */
public class ExistingAppPassword extends Activity {
	EditText first;
	String password = "";
	
	/**
	 * This method is called when the activity is created
	 * Sets the User interface and links variables to XML items
	 */
	@Override
	protected void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.password_dialog_existing);
		first = (EditText)findViewById(R.id.existing_app_password);
		password = getIntent().getExtras().getString("PASSWORD");
	}
	
	/**
	 * Called whenever the OK button is pressed
	 * @param v The view that was pressed
	 */
	public void ok_onclick(View v){
		if(password.equals(first.getText().toString())){
			setResult(RESULT_OK);
			finish();
		} else{
			first.setText("");
			AlertDialog.Builder builder = new AlertDialog.Builder(this);
			builder.setTitle("Wrong password");
			builder.setMessage("You entered an incorrect password.");
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
	 * The method called when the cancel button is pressed
	 * @param v the view that was pressed.
	 */
	public void cancel_onclick(View v){
		setResult(RESULT_CANCELED);
		finish();
	}
}

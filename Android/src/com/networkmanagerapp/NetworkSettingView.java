package com.networkmanagerapp;

import java.util.HashMap;
import java.util.Map;

import android.app.Activity;
import android.app.AlertDialog;
import android.app.AlertDialog.Builder;
import android.content.DialogInterface;
import android.content.Intent;
import android.os.Bundle;
import android.text.method.PasswordTransformationMethod;
import android.view.View;
import android.view.View.OnClickListener;
import android.widget.AdapterView;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.CheckBox;
import android.widget.CompoundButton;
import android.widget.EditText;
import android.widget.Spinner;
import android.widget.TextView;
import android.widget.AdapterView.OnItemSelectedListener;
import android.widget.CompoundButton.OnCheckedChangeListener;

/**
 * This class manages the User Interface for viewing and updating network settings
 * @author rjs07u
 * uses res/layout/{channel_detail.xml, encryption_detail.xml, password_detail.xml, ssid_detail.xml}
 * 
 */
public class NetworkSettingView extends Activity implements
		OnItemSelectedListener, OnClickListener {
	private String label, value;
	private Spinner listOfOptions;
	private EditText mNewValue;
	private TextView mValue;
	private CheckBox showCurPassword;
	private CheckBox showNewPassword;
	private Button updateButton;
	private HashMap<String, String> encryptionTypes;
	private HashMap<String, String> routerEncryptionTypes;

	/**
	 * Called when the activity is first created
	 * Sets the UI based on the data passed in with the Intent that created the activity.
	 * @param savedState data saved to make data persistent over orientation changes
	 */
	@Override
	protected void onCreate(final Bundle savedState) {
		super.onCreate(savedState);
		final Bundle extras = getIntent().getExtras();
		if (extras != null) {
			value = extras.getString("VALUE").trim();
			label = extras.getString("LABEL");
		}
		if (label != null) {
			if (label.contains("SSID")) {
				setContentView(R.layout.ssid_detail);
				mNewValue = (EditText) findViewById(R.id.EditTextNewValue);
				updateButton = (Button) findViewById(R.id.ButtonUpdateSSID);
				updateButton.setOnClickListener(this);
			} else if (label.contains("Channel")) {
				setContentView(R.layout.channel_detail);
				listOfOptions = (Spinner) findViewById(R.id.SpinnerChannel);
				final ArrayAdapter<CharSequence> adapter = ArrayAdapter
						.createFromResource(this, R.array.channel_array,
								android.R.layout.simple_spinner_item);
				adapter
						.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
				listOfOptions.setAdapter(adapter);
				listOfOptions.setOnItemSelectedListener(this);
			} else if (label.contains("Security")) {
				setContentView(R.layout.encryption_detail);
				listOfOptions = (Spinner) findViewById(R.id.SpinnerEncryption);
				final ArrayAdapter<CharSequence> adapter = ArrayAdapter
						.createFromResource(this, R.array.encryption_array,
								android.R.layout.simple_spinner_item);
				adapter
						.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
				listOfOptions.setAdapter(adapter);
				listOfOptions.setOnItemSelectedListener(this);
				encryptionTypes = new HashMap<String, String>();
				routerEncryptionTypes = new HashMap<String, String>();
				getFriendlyEncryptionStrings();
				getRouterEncryptionStrings();
				value = encryptionTypes.get(value);
			} else if (label.contains("Password")) {
				setContentView(R.layout.password_detail);
				mValue = (TextView) findViewById(R.id.TextViewSettingValue);
				mValue
						.setTransformationMethod(new PasswordTransformationMethod());
				showCurPassword = (CheckBox) findViewById(R.id.CheckBoxShowCurrentPassword);
				showCurPassword
						.setOnCheckedChangeListener(new OnCheckedChangeListener() {

							@Override
							public void onCheckedChanged(
									final CompoundButton buttonView,
									final boolean isChecked) {
								if (showCurPassword.isChecked()) {
									mValue.setTransformationMethod(null);
								} else {
									mValue
											.setTransformationMethod(new PasswordTransformationMethod());
								}
							}
						});
				mNewValue = (EditText) findViewById(R.id.EditTextNewPasswordValue);
				mNewValue
						.setTransformationMethod(new PasswordTransformationMethod());
				updateButton = (Button) findViewById(R.id.ButtonUpdatePassword);
				updateButton.setOnClickListener(this);

				showNewPassword = (CheckBox) findViewById(R.id.CheckBoxShowPassword);
				showNewPassword
						.setOnCheckedChangeListener(new OnCheckedChangeListener() {

							@Override
							public void onCheckedChanged(
									final CompoundButton buttonView,
									final boolean isChecked) {
								if (showNewPassword.isChecked()) {
									mNewValue.setTransformationMethod(null);
								} else {
									mNewValue
											.setTransformationMethod(new PasswordTransformationMethod());
								}
							}
						});
			}
		}

		if (mValue == null) {
			mValue = (TextView) findViewById(R.id.TextViewSettingValue);
		}

		TextView mLabel = (TextView) findViewById(R.id.TextViewSettingLabel);

		if (label != null) {
			mLabel.setText(label);
		}

		if (value != null) {
			mValue.setText(value);
		}
	}

	/**
	 * Create a HashMap which maps OPENWRT encryption types to human friendly types
	 */
	private void getFriendlyEncryptionStrings() {
		encryptionTypes.put("none", "None");
		encryptionTypes.put("psk", "WPA");
		encryptionTypes.put("psk2", "WPA2");
		encryptionTypes.put("wep", "WEP");
	}

	/**
	 * Create a HashMap which maps human friendly encryption names onto OPENWRT names.
	 */
	private void getRouterEncryptionStrings() {
		for (Map.Entry<String, String> entry : encryptionTypes.entrySet()) {
			String newKey = entry.getValue();
			String newValue = entry.getKey();
			routerEncryptionTypes.put(newKey, newValue);
		}
	}

	/**
	 * Delegate method called whenever an item is selected from a spinner.
	 * Updates the channel or encryption type depending on the spinner id.
	 * @param adapter the content of the spinner
	 * @param view the spinner that was selected
	 * @param position the selected item position
	 * @param idVal unique id
	 */
	@Override
	public void onItemSelected(final AdapterView<?> adapter, final View view,
			final int position, final long idVal) {
		final String val = ((Spinner) adapter).getSelectedItem().toString();
		if (val != null && !val.equals("-")) {
			mValue.setText(val);
		}
		Intent i;

		if (((View) view.getParent()).getId() == R.id.SpinnerEncryption) {
			String routerEncryptionType = routerEncryptionTypes.get(val);
			if (routerEncryptionType != null) {

				i = new Intent(this, SettingBackgroundUploader.class);
				i.putExtra("CONFIG_FILE_PATH", "networkSettings.ini");
				i.putExtra("KEY", "encryption");
				i.putExtra("VALUE", routerEncryptionType);
				startService(i);
			} else if (((View) view.getParent()).getId() == R.id.SpinnerChannel) {
				i = new Intent(this, SettingBackgroundUploader.class);
				i.putExtra("CONFIG_FILE_PATH", "networkSettings.ini");
				i.putExtra("KEY", "channel");
				i.putExtra("VALUE", val);
				startService(i);
			}
		}
	}

	/**
	 * Delegate method called when nothing is selected in the option menus.
	 * This method does not do anything
	 */
	@Override
	public void onNothingSelected(final AdapterView<?> adapter) {
		// nothing to do
	}

	/**
	 * Called when a button is pressed
	 * Updates SSID or encryption key depending on which button was pressed.
	 * If encryption key, checks value for validity (26 chars, hex)
	 * @param arg0 the button that was pressed.
	 * 
	 */
	@Override
	public void onClick(View arg0) {
		Intent i;
		String val = mNewValue.getText().toString().trim();
		if (val.length() > 0) {
			mValue.setText(val);
			if (arg0.getId() == R.id.ButtonUpdateSSID) {
				i = new Intent(this, SettingBackgroundUploader.class);
				i.putExtra("CONFIG_FILE_PATH", "networkSettings.ini");
				i.putExtra("KEY", "ssid");
				i.putExtra("VALUE", val);
				startService(i);
			} else if (arg0.getId() == R.id.ButtonUpdatePassword) {
				if (val.length() == 26) {
					try{
						Long.parseLong(val, 16);
						i = new Intent(this, SettingBackgroundUploader.class);
						i.putExtra("CONFIG_FILE_PATH", "networkSettings.ini");
						i.putExtra("KEY", "key");
						i.putExtra("VALUE", val);
						startService(i);
					}catch(NumberFormatException ex){
						showKeyFormatError();
					}
					
				}else{
					showKeyFormatError();
				}
			}
		}
	}

	/**
	 * Called when the encryption key is invalid.
	 * Creates an AlertDialog to notify the user.
	 */
	private void showKeyFormatError() {
		Builder builder = new Builder(this);
		builder.setTitle("Invalid Password");
		builder.setMessage(R.string.invalid_key);
		builder.setCancelable(false);
		builder.setPositiveButton(R.string.accept_button_text, new DialogInterface.OnClickListener() {
			
			@Override
			public void onClick(DialogInterface dialog, int arg1) {
				dialog.dismiss();
			}
		});
		AlertDialog dlg = builder.create();
		dlg.show();
	}
}

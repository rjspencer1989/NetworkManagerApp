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
import android.os.Bundle;
import android.widget.TextView;

/**
 * This class is an Activity that displays more details about a currently connected device
 * Uses res/layout/device_detail.xml
 * @author rjs07u
 *
 */
public class DeviceView extends Activity {

	/**
	 * This method is called when the activity is created
	 * It loads the UI and matches UI components to variables.
	 * @param savedState Data that has been saved to maintain state when orientation changes
	 */
	@Override
	protected void onCreate(final Bundle savedState) {
		super.onCreate(savedState);
		
		setContentView(R.layout.device_detail);

		final TextView mDeviceName = (TextView)findViewById(R.id.TextViewDeviceName);
		final TextView mMacAddress = (TextView)findViewById(R.id.TextViewMACAddress);
		final TextView mIpAddress = (TextView)findViewById(R.id.TextViewIPAddress);
		final TextView mDNS = (TextView)findViewById(R.id.dns_list);
		
		final Bundle extras = getIntent().getExtras();
		if(extras != null){
			final String name = extras.getString("DEVICE_NAME");
			final String macAddress = extras.getString("DEVICE_MAC_ADDRESS");
			final String ipAddress = extras.getString("DEVICE_IP_ADDRESS");
			String dns = extras.getString("DEVICE_DNS");
				
			if(name != null){
				mDeviceName.setText(name);
			}
			if(macAddress != null){
				mMacAddress.setText(macAddress);
			}
			if(ipAddress != null){
				mIpAddress.setText(ipAddress);
			}
			
			if(dns != null){
				String[] dnsArray = dns.split(" ");
				StringBuilder b = new StringBuilder();
				for (String query : dnsArray) {
					if(query.contains(".")){
						b.append(query);
						b.append("\n");
					}
				}
				mDNS.setText(b.toString());
			}
		}
	}
}
